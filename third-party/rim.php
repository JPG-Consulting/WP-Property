<?php
/**
 * ArrayFunctionHelper
 *
 * @author Matej Baćo <matejbaco@gmail.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE
*/

class ArrayFunctionHelper
{
	/**
	 * arrayMerge
	 *
	 * Merges named array's.
	 *
	 * Example:
	 * $array_A = array('key_1' => 'value_A_1', 'some_key' => 'some_value');
	 * $array_B = array('key_1' => 'value_B_1', 'key_2' => 'value_B_2');
	 * $result = ArrayFunctionHelper::arrayMerge($array_A, $array_B);
	 *
	 * $result will be:
	 * array(
	 * 	'key_1' => 'value_B_1',
	 * 	'some_key' => 'some_value',
	 * 	'key_2' => 'value_B_2'
	 * )
	*/
	public static function arrayMerge()
	{
		$arg_list = func_get_args();

		$final_array = array();

		foreach ($arg_list as $single_array)
		{
			if (!is_array($single_array))
			{
				throw new Exception('Wrong use, method accepts only arrays.');
			}

			foreach ($single_array as $key => $val)
			{
				$final_array[$key] = $val;
			}
		}

		return $final_array;
	}
}


/**
 * CurlMulti
 *
 * @author Matej Baćo <matejbaco@gmail.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE
*/
class CurlMulti
{
	protected $_curlMultiHandler;

	protected $_processQueue;
	protected $_startTime;
	protected $_stop;

	protected $_threads;
	protected $_urlLookupMap;

	/**
	 * @var integer How many threads to use
	*/
	public $maxThreads = 10;

	/**
	 * @var decimal Limit execution in seconds
	*/
	public $timeLimit = null;

	/**
	 * @var array Default curl thread options
	*/
	public $defaultCurlThreadOptions = array();

	/**
	 * transfer
	 *
	 * Request transfer.
	 *
	 * @param string $url Url to be fetched.
	 * @param array $thread_options Curl thread options.
	 * @param array $callback Callback to be called after transfer or in case of error.
	 * @param array $callback_data Pre existing data for callback.
	*/
	public function transfer($url, $thread_options = array(), $callback = null, &$callback_data = null)
	{
		if (empty($this->maxThreads) || empty($this->defaultCurlThreadOptions))
		{
			throw new Exception('maxThreads or defaultCurlThreadOptions not set');
		}

		$thread_options[CURLOPT_URL] = $url;
		$thread_options = ArrayFunctionHelper::arrayMerge($this->defaultCurlThreadOptions, $thread_options);

		$transfer_data = array(
			'url' => $url,
			'curl_options' => $thread_options,
			'callback' => $callback,
			'callback_data' => &$callback_data
		);
		if (!isset($this->_urlLookupMap[$url]))
		{
			$this->_urlLookupMap[$url] =& $transfer_data;
		}

		$this->_addToProcessQueue($transfer_data);
	}

	/**
	 * process
	 *
	 * Proccess all queued transfer request.
	*/
	public function process()
	{
		$this->_stop = false;
		$this->_startTime = microtime(true);

		if (!$this->_curlMultiHandler)
		{
			$this->_curlMultiHandler = curl_multi_init();
		}

		do
		{
			$this->_fillThreads();

			if ($this->_stop) return;

			$num_of_active_threads = 0;
			$threads_process_status = curl_multi_exec($this->_curlMultiHandler, $num_of_active_threads);

			do
			{
				if ($this->_stop) return;

				$num_of_remaining_msg = 0;
				$thread_data = curl_multi_info_read($this->_curlMultiHandler, $num_of_remaining_msg);

				if ($thread_data)
				{
					if ($thread_data['result'] == CURLE_OK)
					{
						$transfer_error_details = array();
						$recived_data = curl_multi_getcontent($thread_data['handle']);
					}
					else
					{
						$possible_curl_constants = array(
							'CURLE_OK',
							'CURLE_UNSUPPORTED_PROTOCOL',
							'CURLE_FAILED_INIT',
							'CURLE_URL_MALFORMAT',
							'CURLE_URL_MALFORMAT_USER',
							'CURLE_COULDNT_RESOLVE_PROXY',
							'CURLE_COULDNT_RESOLVE_HOST',
							'CURLE_COULDNT_CONNECT',
							'CURLE_FTP_WEIRD_SERVER_REPLY',
							'CURLE_FTP_ACCESS_DENIED',
							'CURLE_FTP_USER_PASSWORD_INCORRECT',
							'CURLE_FTP_WEIRD_PASS_REPLY',
							'CURLE_FTP_WEIRD_USER_REPLY',
							'CURLE_FTP_WEIRD_PASV_REPLY',
							'CURLE_FTP_WEIRD_227_FORMAT',
							'CURLE_FTP_CANT_GET_HOST',
							'CURLE_FTP_CANT_RECONNECT',
							'CURLE_FTP_COULDNT_SET_BINARY',
							'CURLE_PARTIAL_FILE',
							'CURLE_FTP_COULDNT_RETR_FILE',
							'CURLE_FTP_WRITE_ERROR',
							'CURLE_FTP_QUOTE_ERROR',
							'CURLE_HTTP_NOT_FOUND',
							'CURLE_WRITE_ERROR',
							'CURLE_MALFORMAT_USER',
							'CURLE_FTP_COULDNT_STOR_FILE',
							'CURLE_READ_ERROR',
							'CURLE_OUT_OF_MEMORY',
							'CURLE_OPERATION_TIMEOUTED',
							'CURLE_FTP_COULDNT_SET_ASCII',
							'CURLE_FTP_PORT_FAILED',
							'CURLE_FTP_COULDNT_USE_REST',
							'CURLE_FTP_COULDNT_GET_SIZE',
							'CURLE_HTTP_RANGE_ERROR',
							'CURLE_HTTP_POST_ERROR',
							'CURLE_SSL_CONNECT_ERROR',
							'CURLE_FTP_BAD_DOWNLOAD_RESUME',
							'CURLE_FILE_COULDNT_READ_FILE',
							'CURLE_LDAP_CANNOT_BIND',
							'CURLE_LDAP_SEARCH_FAILED',
							'CURLE_LIBRARY_NOT_FOUND',
							'CURLE_FUNCTION_NOT_FOUND',
							'CURLE_ABORTED_BY_CALLBACK',
							'CURLE_BAD_FUNCTION_ARGUMENT',
							'CURLE_BAD_CALLING_ORDER',
							'CURLE_HTTP_PORT_FAILED',
							'CURLE_BAD_PASSWORD_ENTERED',
							'CURLE_TOO_MANY_REDIRECTS',
							'CURLE_UNKNOWN_TELNET_OPTION',
							'CURLE_TELNET_OPTION_SYNTAX',
							'CURLE_OBSOLETE',
							'CURLE_SSL_PEER_CERTIFICATE',
							'CURLE_GOT_NOTHING',
							'CURLE_SSL_ENGINE_NOTFOUND',
							'CURLE_SSL_ENGINE_SETFAILED',
							'CURLE_SEND_ERROR',
							'CURLE_RECV_ERROR',
							'CURLE_SHARE_IN_USE',
							'CURLE_SSL_CERTPROBLEM',
							'CURLE_SSL_CIPHER',
							'CURLE_SSL_CACERT',
							'CURLE_BAD_CONTENT_ENCODING',
							'CURLE_LDAP_INVALID_URL',
							'CURLE_FILESIZE_EXCEEDED',
							'CURLE_FTP_SSL_FAILED'
						);

						$constant = '';
						foreach ($possible_curl_constants as $single_const_name)
						{
							if ($thread_data['result'] == constant($single_const_name))
							{
								$constant = $single_const_name;
								break;
							}
						}

						$transfer_error_details = array(
							'curl_error_number' => $thread_data['result'],
							'curl_error_constant' => $constant
						);
						$recived_data = null;
					}

					$last_http_status_code = curl_getinfo($thread_data['handle'], CURLINFO_HTTP_CODE);

					if ($last_http_status_code < 200 || $last_http_status_code >= 300)
					{
						$recived_data = null;
					}

					$stored_thread_data = $this->_threads[$thread_data['handle']];

					if (isset($stored_thread_data['callback_data']['trace']))
					{
						$stored_thread_data['callback_data']['trace'][] = array(
							'time' => (microtime(true) - $this->_startTime),
							'num_of_threads' => sizeof($this->_threads)
						);
					}

					if (!empty($stored_thread_data['callback']))
					{
						$params = array();
						$params[] = $stored_thread_data['url'];
						$params[] = $recived_data;
						$params[] = $last_http_status_code;
						$params[] =& $stored_thread_data['callback_data'];
						$params[] = $transfer_error_details;
						$params[] =& $this;

						call_user_func_array($stored_thread_data['callback'], $params);
					}

					if ($this->_fillThreads(true))
					{
						$num_of_active_threads++;
					}

					if ($this->_stop) return;

					curl_multi_remove_handle($this->_curlMultiHandler, $thread_data['handle']);
					curl_close($thread_data['handle']);
					unset($this->_threads[$thread_data['handle']]);
				}
			} while ($num_of_remaining_msg > 0);

		} while (!empty($this->_processQueue) || $threads_process_status === CURLM_CALL_MULTI_PERFORM || $num_of_active_threads > 0);

		curl_multi_close($this->_curlMultiHandler);
	}

	/**
	 * stop
	 *
	 * Stops all transfers.
	*/
	public function stop()
	{
		$this->_cleanup();
	}

	/**
	 * getThreadDataByCurlHandler
	 *
	 * Gets storen thread data by curl thread object.
	 *
	 * @param object $curl_handler
	*/
	public function getThreadDataByCurlHandler($curl_handler)
	{
		return (isset($this->_threads[$curl_handler])) ? $this->_threads[$curl_handler] : null;
	}

	/**
	 * numOfThreads
	 *
	 * Current number of working therads.
	*/
	public function numOfThreads()
	{
		return sizeof($this->_threads);
	}

	/**
	 * undoneTransfers
	 *
	 * Transfers wating in queue.
	*/
	public function undoneTransfers()
	{
		return $this->_processQueue;
	}

	/**
	 * _addToProcessQueue
	 *
	 * Add transfer to queue.
	*/
	protected function _addToProcessQueue(&$transfer_data)
	{
		$this->_processQueue[] = &$transfer_data;
	}

	/**
	 * _cleanup
	 *
	 * Rise stop flag for process and reset everything.
	*/
	protected function _cleanup()
	{
		$this->_stop = true;

		foreach ($this->_threads as $thread_handler => $thread_data)
		{
			curl_multi_remove_handle($this->_curlMultiHandler, $thread_data['curl_handler']);
			curl_close($thread_data['curl_handler']);
		}

		curl_multi_close($this->_curlMultiHandler);
	}

	/**
	 * _fillThreads
	 *
	 * Fills working thread pool.
	 *
	 * @param boolean $increase Push addition thread in workig pool.
	*/
	protected function _fillThreads($increase = false)
	{
		if ($this->timeLimit > 0)
		{
			$time_remaning = $this->timeLimit - (microtime(true) - $this->_startTime);
			if ($time_remaning <= 0)
			{
				$this->_cleanup();
				return false;
			}
		}

		$is_filled = false;

		$size_of_threads = sizeof($this->_threads);
		while (
			!empty($this->_processQueue)
			&& (
				($size_of_threads < $this->maxThreads)
				|| (
					$increase
					&& (
						$size_of_threads < ($this->maxThreads + 1)
					)
				)
			)
		)
		{
			if ($this->_stop) return;

			$thread_data =& $this->_processQueue[0];
			array_shift($this->_processQueue);

			$thread_handler = curl_init();
			curl_setopt_array($thread_handler, $thread_data['curl_options']);
			$thread_data['curl_handler'] = $thread_handler;

			$this->_threads[$thread_handler] =& $thread_data;

			curl_multi_add_handle($this->_curlMultiHandler, $thread_handler);
			$is_filled = true;

			$size_of_threads = sizeof($this->_threads);
		}

		return $is_filled;
	}
}


/**
 * rim - Remote Image Library
 *
 * @author Matej Baćo <matejbaco@gmail.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE
*/

class rim
{
	public $profile = false;

	/**
	 * getMultiImageTypeAndSize
	 *
	 * @param array $urls Image urls to fetch, key-es will be preserved
	 * @param array $options array(
	 *	'max_num_of_threads' => (integer) def: 10, // how many threads to use when fetching data
	 *	'time_limit' => (decimal) def: null, // limit total execution time in seconds, 2.34 is 2 seconds and 34 miliseconds
	 *	'callback' => (array) def: null, // callback to call after fetch of each image data
	 *	'curl_connect_timeout' => (integer) def: 2, // curl therad connect timeout
	 *	'curl_timeout' => (integer) def: 3 // curl thread timeout
	 * )
	 */
	public function getMultiImageTypeAndSize($urls, $options = array())
	{
		if (empty($urls) || !is_array($urls))
		{
			throw new Exception('Invalid arguments.');
		}

		$default_options = array(
			'max_num_of_threads' => 10,
			'time_limit' => null,
			'callback' => null,
			'curl_connect_timeout' => 2,
			'curl_timeout' => 3
		);
		$options = ArrayFunctionHelper::arrayMerge($default_options, $options);

		$this->_curlMulti = new CurlMulti();
		$this->_curlMulti->maxThreads = $options['max_num_of_threads'];
		if (!empty($options['time_limit']))
		{
			$this->_curlMulti->timeLimit = $options['time_limit'];
		}

		$this->_curlMulti->defaultCurlThreadOptions = array(
			CURLOPT_RETURNTRANSFER 		=> true,        						// return body
			CURLOPT_HEADER         		=> false,       						// return headers
			CURLOPT_BINARYTRANSFER		=> true,								// raw data
			CURLOPT_FOLLOWLOCATION 		=> true,        						// follow redirects
			CURLOPT_ENCODING       		=> "",          						// handle all encodings
			CURLOPT_USERAGENT     	 	=> "rim_spider",  						// who am i
			CURLOPT_AUTOREFERER   	 	=> true,        						// set referer on redirect
			CURLOPT_CONNECTTIMEOUT	 	=> $options['curl_connect_timeout'],	// timeout on connect
			CURLOPT_TIMEOUT       	 	=> $options['curl_timeout']				// timeout on response
		);

		$images_data = array();
		foreach ($urls as $key => $url)
		{
			$images_data[$key] = array(
				'url' => $url,
				'error' => array()
			);

			if (!empty($options['callback']))
			{
				$images_data[$key]['callback'] = $options['callback'];
			}

			if ($this->profile)
			{
				$images_data[$key]['trace'] = array();
			}

			$this->_getImageData($images_data[$key]);
		}

		$this->_curlMulti->process();

		return $images_data;
	}

	/**
	 * getSingleImageTypeAndSize
	 *
	 * @param array $url Image url to fetch
	 * @param array $options array(
	 *	'time_limit' => (decimal) def: null, // limit total execution time in seconds, 2.34 is 2 seconds and 34 miliseconds
	 *	'callback' => (array) def: null, // callback to call after fetch of each image data
	 *	'curl_connect_timeout' => (integer) def: 2, // curl therad connect timeout
	 *	'curl_timeout' => (integer) def: 3 // curl thread timeout
	 * )
	 */
	public function getSingleImageTypeAndSize($url, $options=array())
	{
		$urls = array($url);

		$default_options = array(
			'time_limit' => null,
			'callback' => null,
			'curl_connect_timeout' => 2,
			'curl_timeout' => 3
		);
		$options = ArrayFunctionHelper::arrayMerge($default_options, $options);

		$this->_curlMulti = new CurlMulti();
		$this->_curlMulti->maxThreads = 1;
		if (!empty($options['time_limit']))
		{
			$this->_curlMulti->timeLimit = $options['time_limit'];
		}
		$this->_curlMulti->defaultCurlThreadOptions = array(
			CURLOPT_RETURNTRANSFER 		=> true,       							// return body
			CURLOPT_HEADER         		=> false,       						// return headers
			CURLOPT_BINARYTRANSFER		=> true,								// raw data
			CURLOPT_FOLLOWLOCATION 		=> true,        						// follow redirects
			CURLOPT_ENCODING       		=> "",          						// handle all encodings
			CURLOPT_USERAGENT     	 	=> "rim_spider",    					// who am i
			CURLOPT_AUTOREFERER   	 	=> true,        						// set referer on redirect
			CURLOPT_CONNECTTIMEOUT	 	=> $options['curl_connect_timeout'],	// timeout on connect
			CURLOPT_TIMEOUT       	 	=> $options['curl_timeout']				// timeout on response
		);

		$data = array(
			'url' => $url
		);
		$this->_getImageData($data);

		$this->_curlMulti->process();

		if (!empty($data['error']))
		{
			return array('error' => $data['error']);
		}

		return $data['image_data'];
	}

	/**
	 * stop
	 *
	 * Stops fetch of images data
	 */
	public function stop()
	{
		$this->_curlMulti->stop();
	}

	/**
	 * _getImageData
	 *
	 * First stop in processing every image.
	*/
	protected function _getImageData(&$data)
	{
		$url = $data['url'];

		if (empty($url))
		{
			$data['error'] = array(
				'code' => 0,
				'description' => 'URL not set'
			);
			$this->_triggerCallback($data);
			return false;
		}

		$options = array(
			CURLOPT_RANGE => '0-1'
		);

		$data['image_data'] = array(
			'type' => null,
			'width' => null,
			'height' => null
		);

		$this->_curlMulti->transfer($url, $options, array($this, '_imageTypeCallback'), $data);
	}

	/**
	 * _imageTypeCallback
	 *
	 * Determinig image type.
	 *
	 * @internal Had to be public so CurlMulti can access it.
	*/
	public function _imageTypeCallback($url, $recived_data, $status_code, &$callback_data, $transfer_error_details)
	{
		if (empty($recived_data))
		{
			$callback_data['error'] = array(
				'code' => 1,
				'description' => 'URL fetch failed',
				'http_status' => $status_code
			);
			$this->_triggerCallback($callback_data);
			return false;
		}

		$data = substr(bin2hex($recived_data), 0, 4);

		switch ($data)
		{
			case 'ffd8': // jpeg
				{
					$callback_data['image_data']['type'] = 'jpeg';

					$options = array();
					$options[CURLOPT_BUFFERSIZE] = '256';
					$options[CURLOPT_RETURNTRANSFER] = '';
					$options[CURLOPT_WRITEFUNCTION] = array($this, "_jpegTransferCallback");

					$callback_data['streamed_buffer'] = '';

					$this->_curlMulti->transfer($url, $options, array($this, '_jpegReadCallback'), $callback_data);
				}
				break;
			case '4749': // gif
				{
					$callback_data['image_data']['type'] = 'gif';

					$options = array();
					$options[CURLOPT_RANGE] = '6-13';
					$this->_curlMulti->transfer($url, $options, array($this, '_gifReadCallback'), $callback_data);
				}
				break;
			case '8950': // png
				{
					$callback_data['image_data']['type'] = 'png';

					$options = array();
					$options[CURLOPT_RANGE] = '16-23';

					$this->_curlMulti->transfer($url, $options, array($this, '_pngReadCallback'), $callback_data);
				}
				break;
			default: // unknown type
				{
					$callback_data['error'] = array(
						'code' => 2,
						'description' => 'unknown image format',
						'http_status' => $status_code
					);

					$this->_triggerCallback($callback_data);
					return false;
				}
		}
	}

	/**
	 * _jpegTransferCallback
	 *
	 * Curl data write buffer function.
	 * Image dimension are on differnt position within a file
	 * so this function will be called when buffer is ready a jpeg can be composed in memory,
	 * once dimension are known futher transfer of jpeg file ends.
	 *
	 * @internal Had to be public so CurlMulti can access it.
	*/
	public function _jpegTransferCallback($ch, $data)
	{
		$size_of_chunk = mb_strlen($data, '8bit');

		$thread_data =& $this->_curlMulti->getThreadDataByCurlHandler($ch);
		$callback_data =& $thread_data['callback_data'];

		if (!isset($callback_data['streamed_buffer']))
			$callback_data['streamed_buffer'] = '';
		$callback_data['streamed_buffer'] .= $data;

		if (strlen($callback_data['streamed_buffer']) < 2)
		{
			return $size_of_chunk;
		}

		// strip magic marker 0xFFD8
		$operationalStreamedData = substr($callback_data['streamed_buffer'], 2);

		do
		{
			// can I read marker
			if (strlen($operationalStreamedData) < 2)
			{
				return $size_of_chunk;
			}

			$info = unpack('nmarker', $operationalStreamedData);
			$operationalStreamedData = substr($operationalStreamedData, 2);

			// only 0xFFC0 is of interest
			if ($info['marker'] != 0xFFC0)
			{
				// can I read length
				if (strlen($operationalStreamedData) < 2)
				{
					return $size_of_chunk;
				}

				// is block whole
				$info = unpack('nlength', $operationalStreamedData);
				if (strlen($operationalStreamedData) < $info['length'])
				{
					return $size_of_chunk;
				}

				$operationalStreamedData = substr($operationalStreamedData, $info['length']);
				continue;
			}

			// 0xFFC0 marker area

			// can I read length
			if (strlen($operationalStreamedData) < 2)
			{
				return $size_of_chunk;
			}

			// is block whole
			$info = unpack('nlength', $operationalStreamedData);
			if (strlen($operationalStreamedData) < $info['length'])
			{
				return $size_of_chunk;
			}
			$operationalStreamedData = substr($operationalStreamedData, 2);

			// get data
			$info = unpack('Cprecision/nY/nX', $operationalStreamedData);

			$callback_data['image_data']['height'] = $info['Y'];
			$callback_data['image_data']['width'] = $info['X'];

			return 0; // stop reading data from source
		} while (!empty($operationalStreamedData));

		return $size_of_chunk;
	}

	/**
	 * _jpegReadCallback
	 *
	 * Will be called when jpeg data is fetched.
	 *
	 * @internal Had to be public so CurlMulti can access it.
	*/
	public function _jpegReadCallback($url, $recived_data, $status_code, &$callback_data, $transfer_error_details)
	{
		if (isset($callback_data['streamed_buffer']))
		{
			unset($callback_data['streamed_buffer']);
		}

		if (empty($callback_data['image_data']['width']))
		{
			$callback_data['error'] = array(
				'code' => 3,
				'description' => 'jpeg image format read failed',
				'http_status' => $status_code
			);
			$this->_triggerCallback($callback_data);
			return;
		}

		$this->_triggerCallback($callback_data);
	}

	/**
	 * _gifReadCallback
	 *
	 * Will be called when gif data is fetched.
	 *
	 * @internal Had to be public so CurlMulti can access it.
	*/
	public function _gifReadCallback($url, $recived_data, $status_code, &$callback_data, $transfer_error_details)
	{
		$imageWH = unpack('vwidth/vheight', $recived_data);

		if (empty($imageWH['width']))
		{
			$callback_data['error'] = array(
				'code' => 3,
				'description' => 'gif image format read failed',
				'http_status' => $status_code
			);
			$this->_triggerCallback($callback_data);
			return;
		}

		$callback_data['image_data']['width'] = $imageWH['width'];
		$callback_data['image_data']['height'] = $imageWH['height'];

		$this->_triggerCallback($callback_data);
	}

	/**
	 * _pngReadCallback
	 *
	 * Will be called when png data is fetched.
	 *
	 * @internal Had to be public so CurlMulti can access it.
	*/
	public function _pngReadCallback($url, $recived_data, $status_code, &$callback_data, $transfer_error_details)
	{
		$imageWH = unpack('Nwidth/Nheight', $recived_data);

		if (empty($imageWH['width']))
		{
			$callback_data['error'] = array(
				'code' => 3,
				'description' => 'png image format read failed',
				'http_status' => $status_code
			);
			$this->_triggerCallback($callback_data);
			return;
		}

		$callback_data['image_data']['width'] = $imageWH['width'];
		$callback_data['image_data']['height'] = $imageWH['height'];

		$this->_triggerCallback($callback_data);
	}

	/**
	 * _triggerCallback
	 *
	 * Will trigger callback in clijent code for every single image.
	*/
	protected function _triggerCallback(&$data)
	{
		if (!isset($data['callback']))
			return;

		$callback = $data['callback'];
		unset($data['callback']);

		if (!empty($callback))
		{
			$params = array();
			$params[] = $data;
			$params[] =& $this;

			call_user_func_array($callback, $params);
		}
	}
}