<?php

class Md5Crypt {

    private $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    protected function to64($v, $n) {
        $itoa64 = $this->itoa64;
        $ret = '';

        while (--$n >= 0) {
            $ret .= $itoa64[$v & 0x3f];
            $v = $v >> 6;
        }
        return $ret;
    }

    public function apache($pw, $salt = NULL) {
        $Magic = '$apr1$';

        return self::unix($pw, $salt, $Magic);
    }

    public function unix_md5_crypt($pw, $salt = NULL, $Magic = '$1$') {
        $itoa64 = $this->itoa64;

        if ($salt !== NULL) {
            // Take care of the magic string if present
            if (substr($salt, 0, strlen($Magic)) == $Magic) {
                $salt = substr($salt, strlen($Magic), strlen($salt));
            }
            // Salt can have up to 8 characters
            $parts = explode('$', $salt, 1);
            $salt = substr($parts[0], 0, 8);
        } else {
            $salt = '';
            mt_srand((double) (microtime() * 10000000));

            while (strlen($salt) < 8) {
                $salt .= $itoa64[mt_rand(0, strlen($itoa64) - 1)];
            }
        }

        $ctx = $pw . $Magic . $salt;

        $final = pack('H*', md5($pw . $salt . $pw));

        for ($pl = strlen($pw); $pl > 0; $pl -= 16) {
            $ctx .= substr($final, 0, ($pl > 16) ? 16 : $pl);
        }

        // Now the 'weird' xform
        for ($i = strlen($pw); $i; $i >>= 1) {
            if ($i & 1) {    // This comes from the original version,
                $ctx .= pack("C", 0);   // where a memset() is done to $final
            } else {     // before this loop
                $ctx .= $pw[0];
            }
        }

        $final = pack('H*', md5($ctx)); // The following is supposed to make
        // things run slower

        for ($i = 0; $i < 1000; $i++) {
            $ctx1 = '';
            if ($i & 1) {
                $ctx1 .= $pw;
            } else {
                $ctx1 .= substr($final, 0, 16);
            }
            if ($i % 3) {
                $ctx1 .= $salt;
            }
            if ($i % 7) {
                $ctx1 .= $pw;
            }
            if ($i & 1) {
                $ctx1 .= substr($final, 0, 16);
            } else {
                $ctx1 .= $pw;
            }
            $final = pack('H*', md5($ctx1));
        }

        // Final xform
        $passwd = '';
        $passwd .= self::to64((intval(ord($final[0])) << 16) | (intval(ord($final[6])) << 8) | (intval(ord($final[12]))), 4);
        $passwd .= self::to64((intval(ord($final[1])) << 16) | (intval(ord($final[7])) << 8) | (intval(ord($final[13]))), 4);
        $passwd .= self::to64((intval(ord($final[2])) << 16) | (intval(ord($final[8])) << 8) | (intval(ord($final[14]))), 4);
        $passwd .= self::to64((intval(ord($final[3])) << 16) | (intval(ord($final[9])) << 8) | (intval(ord($final[15]))), 4);
        $passwd .= self::to64((intval(ord($final[4]) << 16) | (intval(ord($final[10])) << 8) | (intval(ord($final[16])))), 4);
        $passwd .= self::to64((intval(ord($final[11]))), 2);

        // Return the final string
        return $Magic . $salt . '$' . $passwd;
    }

    public function generate_random_string($length = 8) {
        # If you want to add special characters in your random string, uncomment
        # Be sure that you also have did it in your iredmail python configuration file
        # If you proceed with one sided change, then your module will stop to work
        $randomString = '';
        $chars = '23456789' . 'abcdefghjkmnpqrstuvwxyz' . '23456789' . 'ABCDEFGHJKLMNPQRSTUVWXYZ' . '23456789';     # . '@#&*-+' 
        $charactersLength = strlen($chars);
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $chars[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function generate_md5_password($password) {
        return $this->unix_md5_crypt($password, $this->generate_random_string(8));
    }

}

?>