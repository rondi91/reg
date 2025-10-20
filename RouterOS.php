<?php
class RouterOS {
    private $socket;

    public function connect($host, $user, $pass, $port = 8728, $timeout = 3) {
        $this->socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$this->socket) return false;

        $this->writeSentence(['/login']);
        $ret = $this->readSentence();
        if (isset($ret[0]) && strpos($ret[0], '=ret=') === 0) {
            $chal = substr($ret[0], 5);
            $md = md5(chr(0) . $pass . pack('H*', $chal));
            $this->writeSentence(['/login', '=name=' . $user, '=response=00' . $md]);
            $ret = $this->readSentence();
            return isset($ret[0]) && $ret[0] == '!done';
        }
        return false;
    }

    public function writeSentence($words) {
        foreach ($words as $w) $this->writeWord($w);
        $this->writeWord('');
    }

    private function writeWord($w) {
        $len = strlen($w);
        fwrite($this->socket, $this->encodeLength($len) . $w);
    }

    private function encodeLength($len) {
        if ($len < 0x80) return chr($len);
        if ($len < 0x4000) return chr(($len >> 8) | 0x80) . chr($len & 0xFF);
        if ($len < 0x200000) return chr(($len >> 16) | 0xC0) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF);
        if ($len < 0x10000000) return chr(($len >> 24) | 0xE0) . chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF);
        return chr(0xF0) . chr(($len >> 24) & 0xFF) . chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF);
    }

    private function readWord() {
        $len = $this->getLength();
        if ($len === false) return false;
        return $len > 0 ? fread($this->socket, $len) : '';
    }

    private function getLength() {
        $c = ord(fgetc($this->socket));
        if ($c < 0x80) return $c;
        if ($c < 0xC0) return (($c & 0x3F) << 8) + ord(fgetc($this->socket));
        if ($c < 0xE0) return (($c & 0x1F) << 16)
            + (ord(fgetc($this->socket)) << 8)
            + ord(fgetc($this->socket));
        if ($c < 0xF0) return (($c & 0x0F) << 24)
            + (ord(fgetc($this->socket)) << 16)
            + (ord(fgetc($this->socket)) << 8)
            + ord(fgetc($this->socket));
        return false;
    }

    public function readSentence() {
        $res = [];
        while (true) {
            $w = $this->readWord();
            if ($w === '') break;
            if ($w === false) return false;
            $res[] = $w;
        }
        return $res;
    }

    public function query($cmd, $props = []) {
        $q = [$cmd];
        foreach ($props as $p => $v) $q[] = $v;
        $this->writeSentence($q);

        $res = [];
        while (true) {
            $r = $this->readSentence();
            if (!$r) break;
            if ($r[0] == '!done') break;
            if ($r[0] == '!re') {
                $item = [];
                foreach ($r as $w)
                    if (strpos($w, '=') === 0) {
                        [$k, $v] = explode('=', substr($w, 1), 2);
                        $item[$k] = $v;
                    }
                $res[] = $item;
            }
        }
        return $res;
    }

    public function disconnect() {
        fclose($this->socket);
    }
}
