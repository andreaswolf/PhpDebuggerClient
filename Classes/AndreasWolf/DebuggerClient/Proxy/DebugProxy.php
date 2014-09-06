<?php
namespace AndreasWolf\DebuggerClient\Proxy;
use AndreasWolf\DebuggerClient\Core\Bootstrap;
use AndreasWolf\DebuggerClient\Core\DebugSessionHandler;
use AndreasWolf\DebuggerClient\Network\StreamWatcher;
use AndreasWolf\DebuggerClient\Streams\ConnectionListener;
use AndreasWolf\DebuggerClient\Streams\StreamWrapper;


/**
 * Proxy for debugger connections. This will sit and listen for incoming debugger connections,
 * connect to the IDE and send data back and forth between them.
 *
 * TODO make the addresses and ports configurable
 */
class DebugProxy {

	/**
	 * @var bool
	 */
	protected $debug = FALSE;

	/**
	 * The port an IDE will listen on.
	 *
	 * @var int
	 */
	protected $idePort = 9010;

	/**
	 * The address an IDE will listen on
	 *
	 * @var string
	 */
	protected $ideListenAddress = '127.0.0.1';

	/**
	 * @var resource
	 */
	protected $ideStream;

	/**
	 * The port we should listen on for debugger connections
	 *
	 * @var int
	 */
	protected $debuggerPort = 9000;

	/**
	 * The address we should listen on for debugger connections.
	 *
	 * @var string
	 */
	protected $debuggerListenAddress = '0.0.0.0';

	/**
	 * @var resource
	 */
	protected $debuggerListenStream;

	/**
	 * @var resource
	 */
	protected $debuggerDataStream;

	/**
	 * @var string
	 */
	protected $streamUriTemplate = 'tcp://%s:%s';

	/**
	 * The listeners attached to this proxy.
	 *
	 * @var ProxyListener[]
	 */
	protected $listeners = array();


	/**
	 * Runs the debugging session
	 */
	public function run() {
		$this->setUpListenerStream();
		$streamWatcher = new StreamWatcher();
		$debugSessionHandler = new DebugSessionHandler();

		while (true) {
			$streamsToWatch = array(
				$this->debuggerListenStream,
			);
			if (is_resource($this->debuggerDataStream) && feof($this->debuggerDataStream)) {
				$this->destroyStream($this->debuggerDataStream);
			}
			if ($this->debuggerDataStream) {
				$streamsToWatch[] = $this->debuggerDataStream;
				$streamsToWatch[] = $this->ideStream;
			}

			$this->debug("Watching streams for data");
			$streamWatcher->watchAndNotify($streamsToWatch);
			/*if (stream_select($streamsToWatch, $emptyArray, $emptyArray, NULL)) {
				if (in_array($this->debuggerListenStream, $streamsToWatch)) {
					$this->debug("Handling incoming debugger connection");
					$this->handleIncomingDebuggerConnection();
				}
				if (in_array($this->ideStream, $streamsToWatch)) {
					$this->debug("Handling incoming IDE data");
					$this->handleIncomingIdeData();
				}
				if (in_array($this->debuggerDataStream, $streamsToWatch)) {
					$this->debug("Handling incoming debugger data");
					$this->handleIncomingDebuggerData();
				}
			}*/
		}
	}

	protected function destroyStream(&$stream) {
		stream_socket_shutdown($stream, STREAM_SHUT_RDWR);
		$stream = NULL;
	}

	protected function debug($data) {
		if ($this->debug === FALSE) {
			return;
		}
		$lines = explode("\n", $data);
		foreach ($lines as $line) {
			echo "[DEBUG] ", $line, "\n";
		}
	}

	/**
	 * Reads data from the given stream.
	 *
	 * @param resource $stream
	 * @param bool $readLengthFromData If set, the length of data is read from the first chunk of data that is read (for DBGp)
	 * @return string
	 */
	protected function readDataFromStream($stream, $readLengthFromData = FALSE) {
		if (feof($stream)) {
			fclose($stream);
			return '';
		}

		$bytesToRead = 8192;
		$data = '';
		$dataLength = 0;
		while (!feof($stream) || ($readLengthFromData && $dataLength > 0 && strlen($data) < $dataLength)) {
			$input = fread($stream, $bytesToRead);
			$data .= trim($input);

			if ($readLengthFromData) {
				// the length of this data packet is written directly at the beginning of the data, separated by \0
				if ($dataLength === 0) {
					$dataLength = (int)substr($data, 0, strpos($data, "\0"));
					$dataLength += 1 + strlen((string)$dataLength);
					$this->debug("Receiving ", $dataLength, " bytes...");
				}
				$this->debug("Received ", strlen($data), " bytes already");

				if (strlen($data) >= $dataLength) {
					break;
				}
				// adjust receive window
				$bytesToRead = min($bytesToRead, $dataLength - strlen($data) + 1);
				$this->debug("Receive window ", $bytesToRead, "");
			} else {
				$streamMetaData = stream_get_meta_data($stream);
				// unread_bytes should not be used according to the PHP documentation, but there is no apparent reason
				// why – also couldn’t find any information in the sources
				$bytesToRead = min($streamMetaData['unread_bytes'], $bytesToRead);
				if ($streamMetaData['unread_bytes'] <= 0) {
					break;
				}
			}
		}

		return $data;
	}

	/**
	 * Handles data coming from the IDE.
	 */
	protected function handleIncomingIdeData() {
		$data = $this->readDataFromStream($this->ideStream);
		$this->notifyIdeListeners($data);
		$this->debug("[IDE] " . $data . "");
		$this->writeDataToStream($data, $this->debuggerDataStream);
	}

	/**
	 * Handles data coming from the debugger. If there is no data, this might mean that the debugger session
	 * has ended – then the connection to the IDE is closed as well.
	 */
	protected function handleIncomingDebuggerData() {
		$data = $this->readDataFromStream($this->debuggerDataStream, TRUE);
		if ($data === '') {
			// TODO improve this error handling mechanism
			$this->debug("Debugger session has ended");
			// no need to close socket, it was already shut down
			$this->debuggerDataStream = NULL;
			$this->destroyStream($this->ideStream);
			return;
		}
		$this->notifyDebuggerListeners($data);
		$this->debug("[DBG] (" . strlen($data) . ") " . $data);
		$this->writeDataToStream($data, $this->ideStream);
	}

	/**
	 * @param ProxyListener $listener
	 * @return void
	 */
	public function attachListener(ProxyListener $listener) {
		$this->listeners[] = $listener;
	}

	/**
	 * Notifies the listeners about new data that has arrived from the IDE.
	 *
	 * @param string $data
	 */
	protected function notifyIdeListeners($data) {
		foreach ($this->listeners as $listener) {
			$listener->receivedClientData($data);
		}
	}

	/**
	 * Notifies the listeners about new data that has arrived from the debugger.
	 *
	 * @param string $data
	 */
	protected function notifyDebuggerListeners($data) {
		foreach ($this->listeners as $listener) {
			$listener->receivedDebuggerData($data);
		}
	}

	/**
	 * Writes data to the given stream, making sure that all data gets written even for large packages.
	 *
	 * @param string $data
	 * @param resource $stream
	 */
	protected function writeDataToStream($data, $stream) {
		$bytesWritten = 0;
		$bytesToWrite = strlen($data);
		while ($bytesWritten < $bytesToWrite) {
			$return = fwrite($stream, $data . "\0");
			if ($return === FALSE) {
				$this->debug("Error while writing data: ");
				die();
			} else {
				$bytesWritten += $return;
			}
		}
	}

	/**
	 * Creates the debugger listener stream.
	 */
	protected function setUpListenerStream() {
		$this->debuggerListenStream = new StreamWrapper(stream_socket_server(
			sprintf($this->streamUriTemplate, $this->debuggerListenAddress, $this->debuggerPort), $errno, $errstr
		));
		$this->debuggerListenStream->setDataHandler(new ConnectionListener($this->debuggerListenStream));

		$this->debug("Set up streams");
	}

	/**
	 * Accepts an incoming session on the debugger listener port and opens a new connection to the IDE.
	 *
	 * @return void
	 */
	protected function handleIncomingDebuggerConnection() {
		$this->debuggerDataStream = stream_socket_accept($this->debuggerListenStream);

		$this->ideStream = stream_socket_client(
			sprintf($this->streamUriTemplate, $this->ideListenAddress, $this->idePort), $errno, $errstr
		);
	}

}
