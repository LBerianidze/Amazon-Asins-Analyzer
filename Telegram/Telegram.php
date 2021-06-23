<?php
	/**
	 * Created by PhpStorm.
	 * User: Luka
	 * Date: 10.08.2019
	 * Time: 19:42
	 */
	class Telegram
	{
		var $botApiKey;
		var $userID;
		
		public function __construct($botApiKey, $userID)
		{
			$this->botApiKey = $botApiKey;
			$this->userID = $userID;
		}
		
		public function sendMessage($message)
		{
			$chat_id = $this->userID;
			$disable_web_page_preview = null;
			$reply_to_message_id = null;
			$reply_markup = null;
			$data = array(
				'chat_id' => urlencode($chat_id), 'text' => $message, 'disable_web_page_preview' => urlencode($disable_web_page_preview), 'reply_to_message_id' => urlencode($reply_to_message_id), 'reply_markup' => urlencode($reply_markup)
			);
			$url = "https://api.telegram.org/bot$this->botApiKey/sendMessage";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, count($data));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
			return $result;
		}
	}