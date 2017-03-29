<?php

namespace giddyeffects\yiipixu;

use yii\base\Component;
use yii\base\InvalidParamException;

/*
 * Apixu.com API Wrapper Component for Yii2
 * 
 * @package giddyeffects\yiipixu
 * @author Gideon Nyaga
 * @see https://github.com/giddyeffects/yii2-yiipixu
 * 
*/
class Apixu extends Component
{
    /**
     *
     * @var Apixu Apixu 
     */
    //protected $apixu;

    /**
     * @var string Your Apixu API Key
     */
    protected $api_key;
    
    /**
     * @var string Apixu Base Url. Can be either HTTP or HTTPS but we use HTTP. 
     */
    protected $baseUrl = "http://api.apixu.com/v1/{method}.{format}";
    
    /**
     * @var string API method to query.
     * Can be either current weather(current), Forecast (forecast), Search or Autocomplete (search) or History (history)
     * @default current
     */
    public $api_method = "current";
    
    /**
     * @var string Response format. Can be json or xml.
     * @default json
     */
    public $format = "json";
    
    /**
     * @var string The query string based on which data is sent back
     * Enter US Zipcode, UK Postcode, Canada Postalcode, IP address, Latitude/Longitude (decimal degree) or city name
     */
    public $query;
    
    /**
     * @var integer The number of days of forecast required. Required ONLY with forecast API method
     * Values should be in the range 1-10. If no days parameter is provided then only current day's weather is returned
     */
    public $days;
    
    /**
     * @var string Restrict date output for Forecast and History API method. Required for History API.
     * For history API $date should be on or after 1st Jan, 2015 in yyyy-MM-dd format (i.e. 2015-01-01)
     * For forecast API $date should be between current day and the next 10 days also in yyyy-MM-dd format.
     */
    public $date;
    
    /**
     * @var integer Unix timestamp used by the Forecast and History API method.
     * The unit date has the same restrictions as date above. Pass either $date or $unixDate and not both in the same request.
     */
    public $unixDate;
    
    /**
     * @var integer Restricting forecast or history output to a specific hour in a given day.
     * Value MUST be in 24 hour. e.g. 5pm should be hour=17 etc
     */
    public $hour;
    
    /**
     * @var string Language code. Returns 'condition:text' field in the API in the desired language
     */
    public $lang;

    /**
     * @var mixed Response from the API
     */
    public $response;
    
    public function __construct($config = [])
    {
        foreach ($config as $param => $value) {
            $this->$param = $value;
        }
    }
    
    /**
     * Validates the Params
     * 
     * @return void
     * @throws InvalidParamException
     */
    protected function validateParams() {
        if (empty($this->api_key) || empty($this->api_method) || empty($this->format) || empty($this->query)) {
            throw new InvalidParamException('"api_key" or "api_method" or "format" or "query" cannot be empty.');
        }
        if ($this->format != "json" && $this->format != "xml"){
            throw new InvalidParamException('invalid "format"');
        }
        if ($this->api_method != "current" && $this->api_method != "forecast" && $this->api_method != "search" && $this->api_method != "history"){
            throw new InvalidParamException('invalid "api_method"');
        }
        if ($this->api_method=="forecast" && empty($this->days)){
            throw new InvalidParamException('"days" required for the Forecast API method');
        }
        if ($this->api_method=="forecast" && isset($this->date) && !$this->checkDate($this->date)){
            throw new InvalidParamException('invalid "date" should be in yyyy-mm-dd format and between current day and next 10 days');
        }
        if ($this->api_method=="history" && empty($this->date)){
            throw new InvalidParamException('"date" required for the History API method');
        }
        if ($this->api_method=="history" && !$this->checkDate($this->date)){
            throw new InvalidParamException('invalid "date" should be in yyyy-mm-dd format and between 2015-01-01 and current date');
        }
        if (isset($this->days) && (!is_int($this->days) || $this->days < 1 || $this->days > 10)){
            throw new InvalidParamException('invalid "days" should be an integer in the range 1-10');
        }
        if (isset($this->hour) && (!is_int($this->hour) || $this->hour < 0 || $this->hour > 23)){
            throw new InvalidParamException('invalid "hour" should be an integer in the range 0-23');
        }
    }

    public function request() {
        $this->validateParams();
        $ch = curl_init();  
        $url = $this->getUrl();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

        $json_output=curl_exec($ch);
        $this->response = json_decode($json_output);
        curl_close($ch);
        return $this->response;
    }
    
    /**
     * Generate the API endpoint URL with given parameters
     * @return string
     */
    public function getUrl()
    {
        $url = strtr($this->baseUrl, ['{method}' => $this->api_method, '{format}' => $this->format]);
        $url .= "?key=$this->api_key&q=$this->query";
        if ($this->api_method == "forecast" && !isset($this->date)) $url .= "&days=$this->days";
        if ($this->api_method == "history" || (isset($this->date) && $this->api_method=="forecast")) $url .= "&dt=$this->date";
        if (!empty($this->hour)) $url .= "&hour=$this->hour";
        if (!empty($this->lang)) $url .= "&lang=$this->lang";
        return $url;
    }
    
    /**
     * Checks that the date provided is the correct format and is not more than 10 days.
     * For use when $this->date is set and the API method is forecast.
     * @param string $date
     */
    protected function checkDate($date) {
        if($this->api_method=="forecast")
            return ($this->validateFormat($date) && (strtotime($date) >= time()) && (strtotime($date) < strtotime("+10 days")))?true:false;
        else if ($this->api_method=="history"){
            return ($this->validateFormat($date) && (strtotime($date) >= strtotime('2015-01-01')) && (strtotime($date) < time()))?true:false;
        }
    }
    
    protected function validateFormat($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    //@TODO implement unixdate, while checking that it's either $date or $unixdate and not both
}