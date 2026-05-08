<?php

declare( strict_types = 1 );

namespace Ocolin\HyconextLite;

use GuzzleHttp\Exception\GuzzleException;
use Ocolin\HyconextLite\DTO\System;
use Ocolin\HyconextLite\DTO\MacEntry;
use Ocolin\HyconextLite\DTO\PortStatus;
use Ocolin\HyconextLite\DTO\PortSetting;
use Ocolin\HyconextLite\Exceptions\HyconextException;

class HyconextLite
{
    private HTTP $http;

/* CONSTRUCTOR
----------------------------------------------------------------------------- */

    /**
     * @param ?Config $config Configuration data object.
     * @param ?HTTP $http HTTP handler for mocking.
     */
    public function __construct( ?Config $config = null, ?HTTP $http = null )
    {
        $config = $config ?? new Config();
        $this->http = $http ?? new HTTP( config: $config );
    }



/* GET MAC TABLE
----------------------------------------------------------------------------- */

    /**
     * @return MacEntry[] List of MAC entries.
     * @throws GuzzleException HTTP related error.
     * @throws HyconextException Error parsing device MAC table.
     */
    public function getMacTable() : array
    {
        $output = [];
        $response = $this->http->get( endpoint: 'mac_get_dynamic_mac_entries.json' );
        $json = json_decode( json: $response, associative: true  );
        if( !is_array( $json )) {
            throw new HyconextException( message: 'MAC table output not recognized.' );
        }

        foreach( $json as $row )
        {
            if( is_array( $row )) {
                $output[] = new MacEntry(
                          id: self::toInt( $row['Dynamic_idx'] ),
                         mac: self::toString( $row['Dynamic_mac_addr'] ),
                         fid: self::toInt( $row['Dynamic_fid'] ),
                        port: self::toInt( $row['Dynamic_portid'] ),
                    ageTimer: self::toInt( $row['Dynamic_age_timer'] )
                );
            }
        }

        return $output;
    }



/* GET PORT STATUS
----------------------------------------------------------------------------- */

    /**
     * @return PortStatus[] List of Port Statuses.
     * @throws GuzzleException Error in HTTP transport.
     * @throws HyconextException Error parsing Port status.
     */
    public function getPortStatus() : array
    {
        $output = [];
        $response = $this->http->get( endpoint: 'port_statistics.json' );
        $json = json_decode( json: $response, associative: true  );
        if( !is_array( $json )) {
            throw new HyconextException( message: 'Port Status output not recognized.' );
        }

        foreach( $json as $row ) {
            if( is_array( $row )) {
                $output[] = new PortStatus(
                            id: self::toInt( $row['Port_Id'] ),
                        status: self::toString( $row['Port_Status'] ),
                    linkStatus: self::toString( $row['Link_Status'] ),
                     txGoodPkt: self::toInt( $row['TxGoodPkt'] ),
                      txBadPkt: self::toInt( $row['TxBadPkt'] ),
                     rxGoodPkt: self::toInt( $row['RxGoodPkt'] ),
                      rxBadPkt: self::toInt( $row['RxBadPkt'] ),
                );
            }
        }

        return $output;
    }



/* GET PORT SETTINGS
----------------------------------------------------------------------------- */

    /**
     * @return PortSetting[] List of Port settings.
     * @throws GuzzleException Error transporting HTTP.
     * @throws HyconextException Error parsing port settings.
     */
    public function getPortSettings() : array
    {
        $output = [];
        $response = $this->http->get( endpoint: 'port_setting_load.json' );
        $json = json_decode( json: $response, associative: true  );
        if( !is_array( $json )) {
            throw new HyconextException( message: 'Port Settings output not recognized.' );
        }

        foreach( $json as $row ) {
            if( is_array( $row )) {
                $output[] = new PortSetting(
                                 id: self::toInt(    $row['Port_Id'] ),
                             status: self::toString( $row['Port_Status'] ),
                       spdDuplexCfg: self::toString( $row['Spd_Duplex_Cfg'] ),
                    spdDuplexActual: self::toString( $row['Spd_Duplex_Actual'] ),
                        flowCtrlCfg: self::toString( $row['Flow_Ctrl_Cfg'] ),
                     flowCtrlActual: self::toString( $row['Flow_Ctrl_Actual'] ),
                        description: self::toString( $row['port_decription'] ),

                );
            }
        }

        return $output;
    }



/* GET SYSTEM DATA
----------------------------------------------------------------------------- */

    /**
     * @return System System data object.
     * @throws GuzzleException Error in HTTP transport.
     * @throws HyconextException Error parsing system data.
     */
    public function getSystem() : System
    {
        $response = $this->http->get( endpoint: 'status.json' );
        $data = json_decode( json: $response, associative: true  );
        if( !is_array( $data )) {
            throw new HyconextException( message: 'System output not recognized.' );
        }

        $temps = explode(
            separator: ' / ', string: self::toString( $data['temperature'] )
        );
        $tempCelsius    = (float)$temps[0];
        $tempFahrenheit = (float)$temps[1];

        return new System(
               serialNumber: self::toString( $data['sn'] ),
                      model: self::toString( $data['model'] ),
                tempCelsius: $tempCelsius,
             tempFahrenheit: $tempFahrenheit,
                     fanVcc: self::toFloat( $data['fan_vcc'] ),
                    fanVccp: self::toFloat( $data['fan_vccp'] ),
                       ipv4: self::toString( $data['sys_ipv4'] ),
                       ipv6: self::toString( $data['sys_ipv6'] ),
              ipv6LinkLocal: self::toString( $data['sys_ipv6_ll'] ),
                 macAddress: self::toString( $data['sys_macaddr'] ),
            firmwareVersion: self::toString( $data['fw_ver'] ),
            hardwareVersion: self::toString( $data['hw_ver'] ),
                description: self::toString( $data['des'] ),
                     uptime: self::toString( $data['sys_time'] ),
        );
    }



/* CONVERT TO STRING
----------------------------------------------------------------------------- */

    /**
     * @param mixed $value Value to convert to string.
     * @return string String value.
     */
    private static function toString( mixed $value ): string
    {
        return is_scalar( $value ) ? (string)$value : '';
    }



/* CONVERT TO INTEGER
----------------------------------------------------------------------------- */

    /**
     * @param mixed $value Value to convert to integer.
     * @return int Integer value.
     */
    private static function toInt( mixed $value ): int
    {
        return is_numeric( $value ) ? (int)$value : 0;
    }



/* CONVERT MIXED TO FLOAT
----------------------------------------------------------------------------- */

    /**
     * @param mixed $value Value to convert.
     * @return float Float value.
     */
    private static function toFloat( mixed $value ): float
    {
        return is_numeric( $value ) ? (float)$value : 0.0;
    }
}