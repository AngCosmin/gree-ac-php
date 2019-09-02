<?php namespace App;
class Device {
    const DEFAULT_KEY = 'a3K8Bx%2r8Y7#xDh';
    const AC_IP = '192.168.0.139';

    public $mac;
    public $mid;
    public $cid;
    public $name;

    private $key = '';

    public function pair()
    {
        $sPack = '{ "mac": "'.$this->mac.'", "t": "bind", "uid": 0 }';
        $sEncPack = $this->fnEncrypt($sPack, self::DEFAULT_KEY);
        $request = '{
            "cid": "' . $this->cid . '",
            "i": 1,
            "pack": "' . $sEncPack . '",
            "t": "pack",
            "tcid": "app",
            "uid": 0
        }';

        $this->sendData($request);
    }

    private function generateRequest($pack) {
        $encryptedPack = $this->fnEncrypt($pack, $this->key);
        $request = '{
            "t": "pack",
            "i": 0,
            "uid": 0,
            "cid": "' . $this->mac . '",
            "tcid": "",
            "pack": "'. $encryptedPack . '"
        }';
        return $request;
    }

    private function sendData($request) {
        $fp = fsockopen("udp://" . self::AC_IP, 7000, $errno, $errstr);
        fwrite($fp, $request);
        $x = fread($fp, 1024);
    
        if ($x) {
            $oData = json_decode($x);
            $oResponse = json_decode($this->fnDecrypt($oData->pack, self::DEFAULT_KEY));
            if (is_object($oResponse)) {
                $this->key = $oResponse->key;
            }
            fclose($fp);
        }
    }

    private function executeCommand($pack) {
        $request = $this->generateRequest($pack);
        $this->sendData($request);
    }

    public function on() {
        $pack = '{ "opt": ["Pow"], "p": [1], "t": "cmd" }';
        $this->executeCommand($pack);
    }

    public function off() {
        $pack = '{ "opt": ["Pow"], "p": [0], "t": "cmd" }';
        $this->executeCommand($pack);
    }

    public function setSwing($value) {
        // default: 0,
        // full: 1 - swing in full range
        // fixedTop: 2 - fixed in the upmost position (1/5)
        // fixedMidTop: 3 - fixed in the middle-up position (2/5)
        // fixedMid: 4 - fixed in the middle position (3/5)
        // fixedMidBottom: 5 - fixed in the middle-low position (4/5)
        // fixedBottom: 6 - fixed in the lowest position (5/5)
        // swingBottom: 7 - swing in the downmost region (5/5)
        // swingMidBottom: 8 - swing in the middle-low region (4/5)
        // swingMid: 9 - swing in the middle region (3/5)
        // swingMidTop: 10 - swing in the middle-up region (2/5)
        // swingTop: 11 - swing in the upmost region (1/5)
        
        $pack = '{ "opt": ["SwUpDn"], "p": [' . $value . '], "t": "cmd" }';
        $this->executeCommand($pack);
    }

    public function setTemperature($value) {
        $pack = '{ "opt": ["TemUn", "SetTem"], "p": [0, ' . $value . '], "t": "cmd" }';
        $this->executeCommand($pack);
    }

    public function setFanSpeed($value) {
        // value - 0 (auto), 1 (low), 2 (medium - low), 3 (medium), 4 (medium - high), 5 (high)
        $pack = '{ "opt": ["WdSpd"], "p": [' . $value . '], "t": "cmd" }';
        $this->executeCommand($pack);
    }

    function fnEncrypt($sValue, $sSecretKey, $sMethod = 'aes-128-ecb') {
        return base64_encode(openssl_encrypt($sValue, $sMethod, $sSecretKey, OPENSSL_RAW_DATA));
    }

    function fnDecrypt($sValue, $sSecretKey, $sMethod = 'aes-128-ecb') {
        $sText = base64_decode($sValue);
        return openssl_decrypt($sText, $sMethod, $sSecretKey, OPENSSL_RAW_DATA);
    }
}
