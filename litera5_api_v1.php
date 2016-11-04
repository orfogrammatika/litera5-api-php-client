<?php

namespace Litera5 {

    const VERSION = '1.20160509';

    if (!defined('API_SERVER_URL')) {
        define('API_SERVER_URL', 'https://litera5.ru');
    }

    if (!defined('API_IFRAME_URL')) {
        define('API_IFRAME_URL', 'https://litera5.ru');
    }

    function url($url) {
        return API_SERVER_URL . $url;
    }

    function iframeUrl($url) {
        return API_IFRAME_URL . $url;
    }

    function _bool_2_str($val) {
        return ($val) ? 'true' : 'false';
    }

    function _is_filled($val) {
        return ($val !== null && $val !== "");
    }

    function _get_or_null($arr, $key) {
        if (array_key_exists($key, $arr)) {
            return $arr[$key];
        } else {
            return null;
        }
    }

    /**
     * Class Signature
     * Вспомогательный класс для работы с подписями
     */
    class Signature {
        private $api_key;

        function __construct($api_key) {
            $this->api_key = $api_key;
        }

        /**
         * Возвращает подпись для переданного массива параметров.
         * Пример: sign(["значение1", "значение2", "значение3"])
         * @param $values array
         * @return string
         */
        function sign($values) {
            $sig_values = $values;
            array_push($sig_values, $this->api_key);
            $md5_str = implode("", $sig_values);
            return md5($md5_str);
        }

        /**
         * Проверяет правильность подписи для переданного массива параметров
         * @param $signature string
         * @param $values array
         * @return bool
         */
        function is_valid($signature, $values) {
            return strcasecmp($signature, $this->sign($values)) == 0;
        }
    }


    /*****************************************************
     *                                                   *
     *                                                   *
     *                  Litera5 API                      *
     *                                                   *
     *                                                   *
     *****************************************************/
    abstract class BaseAPIRequest {
        protected $time = null;
        protected $company = null;
        protected $signature = null;

        /**
         * @param $company string
         */
        protected function __construct($company) {
            $this->time = "" . time();
            $this->company = $company;
        }

        protected abstract function _query(&$query);

        /**
         * @return array
         */
        private function mk_query() {
            $query = [$this->time, $this->company];
            $this->_query($query);
            return $query;
        }

        protected abstract function _json(&$json);

        /**
         * Возвращает строку с JSON представлением объекта запроса
         * @return string
         */
        function json() {
            $result["time"] = $this->time;
            $result["company"] = $this->company;
            $this->_json($result);
            $result["signature"] = $this->signature;
            return json_encode($result);
        }

        /**
         * Подписывает запрос соответствующим ключом.
         * @param $sign Signature
         * @return $this
         */
        function sign($sign) {
            $this->signature = $sign->sign($this->mk_query());
            return $this;
        }
    }

    abstract class BaseAPIResponse {
        private $sign;

        public $error_code = null;
        public $error_message = null;

        protected $time = null;
        protected $signature = null;

        /**
         * @param $sign Signature
         * @param $http_code int
         * @param $content string
         */
        function __construct($sign, $http_code, $content) {
            $this->sign = $sign;
            if ($http_code == 200) {
                $json = json_decode($content, true);
                $this->time = _get_or_null($json, 'time');
                $this->_parse_json($json);
                $this->signature = _get_or_null($json, "signature");
            } else
                $this->_set_error($http_code, $content);
        }

        /**
         * @param $code int
         * @param $message string
         */
        private function _set_error($code, $message) {
            $this->error_code = $code;
            $this->error_message = $message;
        }

        /**
         * @param $json array
         */
        protected abstract function _parse_json($json);

        /**
         * Запрос успешно завершился
         * @return bool
         */
        function is_success() {
            return $this->error_code == null;
        }

        /**
         * Запрос окончился неудачей
         * @return bool
         */
        function is_failure() {
            return !$this->is_success();
        }

        /**
         * @param $query array
         */
        protected abstract function _query(&$query);

        /**
         * @return array
         */
        protected function mk_query() {
            $query = [$this->time];
            $this->_query($query);
            return $query;
        }

        /**
         * Проверяет подпись ответа
         * @return bool
         */
        function has_valid_signature() {
            return $this->sign->is_valid($this->signature, $this->mk_query());
        }

    }

    /**
     * Запрос на установку или обновление настроек API
     * Class SetupRequest
     * @package Litera5
     */
    class SetupRequest extends BaseAPIRequest {
        private $on_save_corrected = null;
        private $on_iframe_failure = null;
        private $on_initial_stats = null;
        private $return_icon = null;
        private $return_caption = null;
        private $cancel_icon = null;
        private $cancel_caption = null;
        private $allow_resize_images = null;
        private $show_cancel_button = null;
        private $editor_css = null;
        private $get_stats = null;


        /**
         * @param $val string
         * @return $this
         */
        function on_save_corrected($val) {
            $this->on_save_corrected = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function on_iframe_failure($val) {
            $this->on_iframe_failure = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function on_initial_stats($val) {
            $this->on_initial_stats = $val;
            return $this;
        }

        /**
         * @param $val
         * @return $this
         */
        function return_icon($val) {
            $this->return_icon = $val;
            return $this;
        }

        /**
         * @param $val
         * @return $this
         */
        function return_caption($val) {
            $this->return_caption = $val;
            return $this;
        }

        /**
         * @param $val
         * @return $this
         */
        function cancel_icon($val) {
            $this->cancel_icon = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function cancel_caption($val) {
            $this->cancel_caption = $val;
            return $this;
        }

        /**
         * @param $val boolean
         * @return $this
         */
        function allow_resize_images($val) {
            $this->allow_resize_images = $val;
            return $this;
        }

        /**
         * @param $val boolean
         * @return $this
         */
        function show_cancel_button($val) {
            $this->show_cancel_button = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function editor_css($val) {
            $this->editor_css = $val;
            return $this;
        }

        /**
         * @param $val boolean
         * @return $this
         */
        function get_stats($val) {
            $this->get_stats = $val;
            return $this;
        }

        protected function _query(&$query) {
            if (_is_filled($this->on_save_corrected)) array_push($query, $this->on_save_corrected);
            if (_is_filled($this->on_iframe_failure)) array_push($query, $this->on_iframe_failure);
            if (_is_filled($this->on_initial_stats)) array_push($query, $this->on_initial_stats);
            if (_is_filled($this->return_icon)) array_push($query, $this->return_icon);
            if (_is_filled($this->return_caption)) array_push($query, $this->return_caption);
            if (_is_filled($this->cancel_icon)) array_push($query, $this->cancel_icon);
            if (_is_filled($this->cancel_caption)) array_push($query, $this->cancel_caption);
            if (_is_filled($this->allow_resize_images)) array_push($query, _bool_2_str($this->allow_resize_images));
            if (_is_filled($this->show_cancel_button)) array_push($query, _bool_2_str($this->show_cancel_button));
            if (_is_filled($this->editor_css)) array_push($query, $this->editor_css);
            if (_is_filled($this->get_stats)) array_push($query, _bool_2_str($this->get_stats));
        }

        protected function _json(&$json) {
            if (_is_filled($this->on_save_corrected)) $json["onSaveCorrected"] = $this->on_save_corrected;
            if (_is_filled($this->on_iframe_failure)) $json["onIFrameFailure"] = $this->on_iframe_failure;
            if (isset($this->on_initial_stats)) $json["onInitialStats"] = $this->on_initial_stats;
            if (_is_filled($this->return_icon)) $json["returnIcon"] = $this->return_icon;
            if (_is_filled($this->return_caption)) $json["returnCaption"] = $this->return_caption;
            if (_is_filled($this->cancel_icon)) $json["cancelIcon"] = $this->cancel_icon;
            if (_is_filled($this->cancel_caption)) $json["cancelCaption"] = $this->cancel_caption;
            if (_is_filled($this->allow_resize_images)) $json["allowResizeImages"] = $this->allow_resize_images;
            if (_is_filled($this->show_cancel_button)) $json["showCancelButton"] = $this->show_cancel_button;
            if (_is_filled($this->editor_css)) $json["editorCss"] = $this->editor_css;
            if (_is_filled($this->get_stats)) $json["getStats"] = $this->get_stats;
        }

        /**
         * Создаёт новый объект запроса
         * @param $company string
         * @return SetupRequest
         */
        static function start($company) {
            return new SetupRequest($company);
        }

    }

    class SetupResponse extends BaseAPIResponse {
        /**
         * @param $json array
         */
        protected function _parse_json($json) {
        }

        /**
         * @param $query array
         */
        protected function _query(&$query) {
        }
    }

    /**
     * Типы разрешений для пользователя
     * Class UserPermission
     * @package Litera5
     */
    class UserPermission {
        /**
         * Пользователь имеет право работать с корпоративным словарём. Добавлять новые слова, редактировать словарь
         */
        const USE_DICTIONARY = "USE_DICTIONARY";
        /**
         * Пользователю запрещено работать с "Синомимами/Эпитетами"
         */
        const DISABLE_CICERO = "DISABLE_CICERO";
        /**
         * Пользователю запрещено при работе через апи делать повторные проверки
         */
        const DISABLE_API_SECONDARY_ORFO_CHECKS = "DISABLE_API_SECONDARY_ORFO_CHECKS";
    }

    /**
     * Типы ошибок на закладке "Правописание"
     * Class OrthoKind
     * @package Litera5
     */
    class OrthoKind {
        /**
         * Орфография
         */
        const SPELLING = "mkSpelling";
        /**
         * Грамматика
         */
        const GRAMMAR = "mkGrammar";
        /**
         * Пунктуация
         */
        const PUNCTUATION = "mkPunctuation";
        /**
         * Стилистика
         */
        const STYLE = "mkStyle";
        /**
         * Семантика
         */
        const SEMANTIC = "mkSemantic";
        /**
         * Типографика
         */
        const TYPOGRAPHY = "mkTypography";
        /**
         * Орфоэпия
         */
        const ORTHOEPY = "mkOrthoepy";
        /**
         * Буква Ё
         */
        const YO = "mkYo";
        /**
         * Оформление
         */
        const PAPER_STRUCTURE = "mkPaperStructure";
    }

    /**
     * Типы ошибок на закладке "Синонимы/эпитеты"
     * Class CiceroKind
     * @package Litera5
     */
    class CiceroKind {
        /**
         * Синонимы
         */
        const SYNONYM = "mkSynonym";
        /**
         * Эпитеты
         */
        const EPITHET = "mkEpithet";
    }

    /**
     * Запрос на создание или обновление информации о пользователях
     * Class UserRequest
     * @package Litera5
     */
    class UserRequest extends BaseAPIRequest {
        private $login = null;
        private $name = null;
        private $password = null;
        private $permissions = null;
        private $ortho_kinds = null;
        private $cicero_kinds = null;

        /**
         * @param $company string
         * @param $login string
         */
        protected function __construct($company, $login) {
            parent::__construct($company);
            $this->login = $login;
        }

        /**
         * @param $val string
         * @return $this
         */
        function name($val) {
            $this->name = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function password($val) {
            $this->password = $val;
            return $this;
        }

        /**
         * @see @UserPermission
         * @param $val array
         * @return $this
         */
        function permissions($val) {
            $this->permissions = $val;
            return $this;
        }

        /**
         * @see @OrthoKind
         * @param $val array
         * @return $this
         */
        function ortho_kinds($val) {
            $this->ortho_kinds = $val;
            return $this;
        }

        /**
         * @see @CiceroKind
         * @param $val array
         * @return $this
         */
        function cicero_kinds($val) {
            $this->cicero_kinds = $val;
            return $this;
        }

        protected function _query(&$query) {
            array_push($query, $this->login);
            if (_is_filled($this->name)) array_push($query, $this->name);
            if (_is_filled($this->password)) array_push($query, $this->password);
            if (_is_filled($this->permissions)) {
                foreach ($this->permissions as $p) {
                    array_push($query, $p);
                }
            };
            if (_is_filled($this->ortho_kinds)) {
                foreach ($this->ortho_kinds as $p) {
                    array_push($query, $p);
                }
            };
            if (_is_filled($this->cicero_kinds)) {
                foreach ($this->cicero_kinds as $p) {
                    array_push($query, $p);
                }
            };
        }

        protected function _json(&$json) {
            $json["login"] = $this->login;
            if (_is_filled($this->name)) $json["name"] = $this->name;
            if (_is_filled($this->password)) $json["password"] = $this->password;
            if (_is_filled($this->permissions)) $json["permissions"] = $this->permissions;
            if (_is_filled($this->ortho_kinds)) $json["orthoKinds"] = $this->ortho_kinds;
            if (_is_filled($this->cicero_kinds)) $json["ciceroKinds"] = $this->cicero_kinds;
        }

        /**
         * Создаёт новый объект запроса
         * @param $company string
         * @param $login string
         * @return UserRequest
         */
        static function start($company, $login) {
            return new UserRequest($company, $login);
        }
    }

    class UserResponse extends BaseAPIResponse {

        public $password = null;

        protected function _parse_json($json) {
            $this->password = _get_or_null($json, "password");
        }

        protected function _query(&$query) {
            if (_is_filled($this->password)) array_push($query, $this->password);
        }

    }

    /**
     * Class CheckRequest
     * Запрос на проверку документа
     */
    class CheckRequest extends BaseAPIRequest {
        private $login = null;
        private $token = null;
        private $document = null;
        private $name = null;
        private $title = null;
        private $description = null;
        private $keywords = null;
        private $custom = null;
        private $html = null;

        /**
         * @param $company string
         * @param $login string
         * @param $token string
         */
        protected function __construct($company, $login, $token) {
            parent::__construct($company);
            $this->login = $login;
            $this->token = $token;
        }

        protected function _query(&$query) {
            array_push($query, $this->login);
            array_push($query, $this->token);
            if (_is_filled($this->document)) array_push($query, $this->document);
            if (_is_filled($this->name)) array_push($query, $this->name);
            if (_is_filled($this->title)) array_push($query, $this->title);
            if (_is_filled($this->description)) array_push($query, $this->description);
            if (_is_filled($this->keywords)) array_push($query, $this->keywords);
            if (_is_filled($this->custom)) {
                foreach ($this->custom as $custom) {
                    array_push($query, $custom["name"]);
                    array_push($query, $custom["value"]);
                }
            }
            if (_is_filled($this->html)) array_push($query, $this->html);
        }

        protected function _json(&$json) {
            $json["login"] = $this->login;
            $json["token"] = $this->token;
            if (_is_filled($this->document)) $json["document"] = $this->document;
            if (_is_filled($this->name)) $json["name"] = $this->name;
            if (_is_filled($this->title)) $json["title"] = $this->title;
            if (_is_filled($this->description)) $json["description"] = $this->description;
            if (_is_filled($this->keywords)) $json["keywords"] = $this->keywords;
            if (_is_filled($this->custom)) $json["custom"] = $this->custom;
            if (_is_filled($this->html)) $json["html"] = $this->html;
        }

        /**
         * @param $val string
         * @return $this
         */
        function document($val) {
            $this->document = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function name($val) {
            $this->name = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function title($val) {
            $this->title = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function description($val) {
            $this->description = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function keywords($val) {
            $this->keywords = $val;
            return $this;
        }

        /**
         * @param $name string
         * @param $value string
         * @return $this
         */
        function custom($name, $value) {
            if ($this->custom == null)
                $this->custom = [];
            array_push($this->custom, array(
                "name" => $name,
                "value" => $value
            ));
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function html($val) {
            $this->html = $val;
            return $this;
        }

        /**
         * Создаёт новый объект запроса
         * @param $company string
         * @param $login string
         * @param $token string
         * @return CheckRequest
         */
        static function start($company, $login, $token) {
            return new CheckRequest($company, $login, $token);
        }
    }

    /**
     * Class CheckResponse
     * Ответ на запрос о проверке документа
     */
    class CheckResponse extends BaseAPIResponse {

        public $document = null;
        public $url = null;

        protected function _parse_json($json) {
            $this->document = _get_or_null($json, "document");
            $this->url = _get_or_null($json, "url");
        }

        protected function _query(&$query) {
            array_push($query, $this->document);
            array_push($query, $this->url);
        }

    }

    /**
     * Class API
     * API для проверки документов на сайте litera5.ru
     * @package Litera5
     */
    class API {

        private $sign;

        function __construct($apiKey) {
            $this->sign = new Signature($apiKey);
        }

        function _query($url, $request) {
            $data_string = $request;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($data_string))
            );
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');

            $data = curl_exec($ch);

            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            $body = $data;

            curl_close($ch);

            return array(
                'code' => $code,
                'body' => $body
            );
        }

        /**
         * Производит настройку API
         * @param $request SetupRequest
         * @return SetupResponse
         */
        function setup($request) {
            $resp = $this->_query(url("/api/pub/setup/"), $request->sign($this->sign)->json());
            return new SetupResponse($this->sign, $resp['code'], $resp['body']);
        }

        /**
         * Настраивает пользователей
         * @param $request UserRequest
         * @return UserResponse
         */
        function user($request) {
            $resp = $this->_query(url("/api/pub/user/"), $request->sign($this->sign)->json());
            return new UserResponse($this->sign, $resp['code'], $resp['body']);
        }

        /**
         * Инициирует процедуру проверки документа
         * @param $request CheckRequest
         * @return CheckResponse
         */
        function check($request) {
            $resp = $this->_query(url("/api/pub/check/"), $request->sign($this->sign)->json());
            return new CheckResponse($this->sign, $resp['code'], $resp['body']);
        }

        /**
         * Отменяет ранее инициированную процедуру проверки документа.
         * @param $request CancelRequest
         * @return CancelResponse
         */
        function cancel($request) {
            $resp = $this->_query(url("/api/pub/cancel/"), $request->sign($this->sign)->json());
            return new CancelResponse($this->sign, $resp['code'], $resp['body']);
        }
    }

    /*****************************************************
     *                                                   *
     *                                                   *
     *                    CMS API                        *
     *                                                   *
     *                                                   *
     *****************************************************/
    abstract class BaseCMSRequest {
        private $sign = null;

        protected $time = null;
        protected $signature = null;

        /**
         * @param $sign Signature
         * @param $json array
         */
        function __construct($sign, $json) {
            $this->sign = $sign;
            $this->time = _get_or_null($json, 'time');
            $this->signature = _get_or_null($json, 'signature');
            $this->_parse_json($json);
        }

        protected abstract function _parse_json($json);

        protected abstract function _query(&$query);

        /**
         * @return array
         */
        private function mk_query() {
            $result = [$this->time];
            $this->_query($result);
            return $result;
        }

        /**
         * Проверяет подпись запроса
         * @return bool
         */
        function has_valid_signature() {
            return $this->sign->is_valid($this->signature, $this->mk_query());
        }
    }

    abstract class BaseCMSResponse {
        protected $time = null;
        protected $signature = null;

        function __construct() {
            $this->time = "" . time();
        }

        protected abstract function _query(&$query);

        protected abstract function _json(&$json);

        /**
         * @return array
         */
        private function mk_query() {
            $result = [$this->time];
            $this->_query($result);
            return $result;
        }

        /**
         * @return string
         */
        function json() {
            $result = array();
            $result["time"] = $this->time;
            $this->_json($result);
            $result["signature"] = $this->signature;
            return json_encode($result);
        }

        /**
         * Подписывает запрос соответствующим ключом.
         * @param $sign Signature
         * @return $this
         */
        function sign($sign) {
            $this->signature = $sign->sign($this->mk_query());
            return $this;
        }
    }

    /**
     * Class SaveCorrectedRequest
     * Запрос на сохранение откорректированного текста в CMS
     * @package Litera5
     */
    class SaveCorrectedRequest extends BaseCMSRequest {

        public $token = null;
        public $title = null;
        public $description = null;
        public $keywords = null;
        private $custom_src = null;
        public $custom = null;
        public $html = null;
        public $stats = null;

        protected function _parse_json($json) {
            $this->token = _get_or_null($json, 'token');
            $this->title = _get_or_null($json, 'title');
            $this->description = _get_or_null($json, 'description');
            $this->keywords = _get_or_null($json, 'keywords');
            $this->custom_src = _get_or_null($json, 'custom');
            $this->custom = array();
            if (_is_filled($this->custom_src)) {
                foreach ($this->custom_src as $custom) {
                    $this->custom[$custom["name"]] = $custom["value"];
                }
            }
            $this->html = _get_or_null($json, 'html');
            $this->stats = _get_or_null($json, 'stats');
        }

        protected function _query(&$query) {
            array_push($query, $this->token);
            if (_is_filled($this->title))
                array_push($query, $this->title);
            if (_is_filled($this->description))
                array_push($query, $this->description);
            if (_is_filled($this->keywords))
                array_push($query, $this->keywords);
            if (_is_filled($this->custom_src)) {
                foreach ($this->custom_src as $custom) {
                    array_push($query, $custom["name"]);
                    array_push($query, $custom["value"]);
                }
            }
            if (_is_filled($this->html))
                array_push($query, $this->html);
        }

    }

    class IFrameFailureRequest extends BaseCMSRequest {

        public $token = null;
        public $url = null;
        public $code = null;
        public $message = null;

        protected function _parse_json($json) {
            $this->token = _get_or_null($json, 'token');
            $this->url = _get_or_null($json, 'url');
            $this->code = _get_or_null($json, 'code');
            $this->message = _get_or_null($json, 'message');
        }

        protected function _query(&$query) {
            array_push($query, $this->token);
            array_push($query, $this->url);
            array_push($query, $this->code);
            array_push($query, $this->message);
        }
    }

    abstract class RedirectUrlResponse extends BaseCMSResponse {
        private $url = null;

        function __construct($url) {
            parent::__construct();
            $this->url = $url;
        }

        protected function _query(&$query) {
            array_push($query, $this->url);
        }

        protected function _json(&$json) {
            $json["url"] = $this->url;
        }
    }

    class SaveCorrectedResponse extends RedirectUrlResponse {
    }

    class IFrameFailureResponse extends RedirectUrlResponse {
    }

    /**
     * Class InitialStatsRequest
     * Запрос на сохранение откорректированного текста в CMS
     * @package Litera5
     */
    class InitialStatsRequest extends BaseCMSRequest {

        public $token = null;
        public $error = null;
        public $stats = null;

        protected function _parse_json($json) {
            $this->token = _get_or_null($json, 'token');
            $this->error = _get_or_null($json, 'error');
            $this->stats = _get_or_null($json, 'stats');
        }

        protected function _query(&$query) {
            array_push($query, $this->token);
            if (_is_filled($this->error))
                array_push($query, $this->error);
        }

    }

    class InitialStatsResponse extends BaseCMSResponse {

        public $cancel = false;

        function __construct($cancel) {
            parent::__construct();
            $this->cancel = $cancel;
        }

        protected function _query(&$query) {
            array_push($query, _bool_2_str($this->cancel));
        }

        protected function _json(&$json) {
            $json['cancel'] = $this->cancel;
        }
    }

    /**
     * Class CMS
     * API для расширения функциональности CMS для работы с Litera5
     * @package Litera5
     */
    class CMS {
        private $sign;

        function __construct($apiKey) {
            $this->sign = new Signature($apiKey);
        }

        private function _get_post_data() {
            $json = file_get_contents('php://input');
            return json_decode($json, true);
        }

        private function _respondJson($json) {
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Length: ' . strlen($json));
            echo $json;
        }

        /**
         * Обрабатывает текущий запрос и возвращает его удобную модель
         * @return SaveCorrectedRequest
         */
        function extractSaveCorrectedRequest() {
            return new SaveCorrectedRequest($this->sign, $this->_get_post_data());
        }

        /**
         * Создаёт подписанную модель ответа и возвращает его в виде строки JSON
         * @param $url string ссылка для редиректа
         */
        function respondSaveCorrectedResponseJSON($url) {
            $response = new SaveCorrectedResponse($url);
            $this->_respondJson($response->sign($this->sign)->json());
        }

        function extractIFrameFailureRequest() {
            return new IFrameFailureRequest($this->sign, $this->_get_post_data());
        }

        function respondIFrameFailureResponseJSON($url) {
            $response = new IFrameFailureResponse($url);
            $this->_respondJson($response->sign($this->sign)->json());
        }

        /**
         * Обрабатывает текущий запрос и возвращает его удобную модель
         * @return InitialStatsRequest
         */
        function extractInitialStatsRequest() {
            return new InitialStatsRequest($this->sign, $this->_get_post_data());
        }

        /**
         * Создаёт подписанную модель ответа и возвращает его в виде строки JSON
         * @param $cancel boolean если true, то дальнейшую работу над документом нужно отменить
         */
        function respondInitialStatsResponseJSON($cancel) {
            $response = new InitialStatsResponse($cancel);
            $this->_respondJson($response->sign($this->sign)->json());
        }
    }

}
