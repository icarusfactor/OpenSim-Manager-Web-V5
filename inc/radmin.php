<?php
/***********************************************************************
Copyright (c) 2008, The New World Grid Regents http://www.newworldgrid.com and Contributors 
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
    * Neither the name of the New World Grid nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; 
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, 
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
***********************************************************************/

// How to instantiate a RemoteAdmin object ?
// $myremoteadmin = new RemoteAdmin("mySimulatorURL", Port, "secret password")

// How to send commands to remoteadmin plugin ?
// $myremoteadmin->SendCommand('admin_broadcast', array('message' => 'Message to broadcast to all regions'));
// $myremoteadmin->SendCommand('admin_shutdown');
// Commands like admin_shutdown don't need params, so you can left the second SendCommand functino param empty ;)

// Example for error handling
// 
// include('classes/RemoteAdmin.php');
// $RA = new RemoteAdmin('localhost', 9000, 'secret');
// $retour = $RA->SendCommand('admin_shutdown');
// if ($retour === FALSE)
// {
//      echo 'ERROR';
// }

class RemoteAdmin
{
    function __construct($sURL, $sPort, $pass)
    {
        $this->simulatorURL = $sURL;   // String
        $this->simulatorPort = $sPort; // Integer
        $this->password = $pass;
    }
                
    function SendCommand($command, $params=array())
    {
        $paramsNames = array_keys($params);
        $paramsValues = array_values($params);

        #<value><string>' . htmlspecialchars($this->password) . '</string></value>
        // Building the XML data to pass to RemoteAdmin through XML-RPC ;)
        $xml = '<?xml version="1.0"?>
            <methodCall>
            <methodName>' . htmlspecialchars($command) . '</methodName>
                <params>
                <param>
                <value>
                <struct>
                <member>
                <name>password</name>
                <value><string>E6nb42ET</string></value>
            </member>';
        if (count($params) != 0)
        {
            for ($p = 0; $p < count($params); $p++)
            {
                $xml .= '<member><name>' . htmlspecialchars($paramsNames[$p]) . '</name>';
                $xml .= '<value>' . htmlspecialchars($paramsValues[$p]) . '</value></member>';
            }
        }

		$xml .= '</struct></value></param></params></methodCall>';

        
        $host = $this->simulatorURL;  
        $port = $this->simulatorPort;
        $timeout = 5; 

    $ch = curl_init( "http://127.0.0.1:9000" );

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_ENCODING ,"ISO-8859-1");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT,'OSMW 5.7');
    #curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $response = curl_exec($ch); 
    $curl_errorno = curl_errno( $ch );
    $curl_error   = curl_error( $ch );
    curl_close($ch); 

    // check for curl errors
    if ( $curl_errorno != 0 ) {
      die( "Curl ERROR: {$curl_errorno} - {$curl_error}n" );
    }


            $response = substr($res, strpos($res, "\r\n\r\n"));;
            
			// Now parsing the XML response from RemoteAdmin ;)
            $result = array();
            
			if (preg_match_all('#<name>(.+)</name><value><(string|int)>(.*)</\2></value>#U', $response, $regs, PREG_SET_ORDER))
		    {
                foreach($regs as $key=>$val)
                {
                    $result[$val[1]] = $val[3];
                }
            }
            return $result;
    }
}
?>
