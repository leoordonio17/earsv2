<?php

namespace backend\components;

use Yii;
use yii\base\Component;

/**
 * PIDS API Component
 * Handles integration with PIDS DTS API for personnel authentication and data
 */
class PidsApiComponent extends Component
{
    /**
     * @var string API Base URL
     */
    public $apiBaseUrl = 'https://dts.pids.gov.ph/api';
    
    /**
     * @var string API Token
     */
    public $apiToken = 'db7af4b8bf265411f6e9ee3b6b78eabaa2ed3e9cb6d1b1239dbf2e86984d127d';

    /**
     * Make HTTP GET request using cURL
     * 
     * @param string $url The URL to request
     * @return array|null Response data or null on failure
     */
    private function httpGet($url)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            // SSL/TLS Configuration
            // Try with SSL verification first (production standard)
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            
            // Specify minimum TLS version (TLS 1.2+)
            if (defined('CURL_SSLVERSION_TLSv1_2')) {
                curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
            }
            
            // Set CA bundle path if available (helps with SSL verification)
            $caBundlePaths = [
                '/etc/ssl/certs/ca-certificates.crt', // Debian/Ubuntu/Gentoo
                '/etc/pki/tls/certs/ca-bundle.crt',   // Fedora/RHEL/CentOS
                '/etc/ssl/ca-bundle.pem',              // OpenSUSE
                '/etc/ssl/cert.pem',                   // OpenBSD
                '/usr/local/share/certs/ca-root-nss.crt', // FreeBSD
            ];
            
            foreach ($caBundlePaths as $path) {
                if (file_exists($path)) {
                    curl_setopt($ch, CURLOPT_CAINFO, $path);
                    break;
                }
            }
            
            // Timeout settings
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15); // Connection timeout
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);         // Total timeout
            
            // User agent
            curl_setopt($ch, CURLOPT_USERAGENT, 'EARS-PIDS-Integration/2.0');
            
            // Additional options for debugging
            curl_setopt($ch, CURLOPT_VERBOSE, YII_DEBUG);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            $info = curl_getinfo($ch);
            
            curl_close($ch);
            
            // Log detailed error information
            if ($error) {
                Yii::error([
                    'message' => 'cURL Error',
                    'error' => $error,
                    'errno' => $errno,
                    'url' => $url,
                    'http_code' => $httpCode,
                    'total_time' => $info['total_time'] ?? 0,
                    'connect_time' => $info['connect_time'] ?? 0,
                ], __METHOD__);
                
                // If SSL error, try again without SSL verification as fallback
                if (in_array($errno, [60, 77])) { // SSL certificate problem or Problem with the SSL CA cert
                    Yii::warning('Retrying API request without SSL verification (fallback)', __METHOD__);
                    return $this->httpGetWithoutSSL($url);
                }
                
                return null;
            }
            
            if ($httpCode !== 200) {
                Yii::error([
                    'message' => 'HTTP Error',
                    'http_code' => $httpCode,
                    'url' => $url,
                    'response_preview' => substr($response, 0, 500),
                ], __METHOD__);
                return null;
            }
            
            $decoded = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Yii::error([
                    'message' => 'JSON Decode Error',
                    'error' => json_last_error_msg(),
                    'response_preview' => substr($response, 0, 500),
                ], __METHOD__);
                return null;
            }
            
            return $decoded;
            
        } catch (\Exception $e) {
            Yii::error('Exception in httpGet: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }
    
    /**
     * Fallback HTTP GET without SSL verification
     * Only used when SSL verification fails
     * 
     * @param string $url The URL to request
     * @return array|null Response data or null on failure
     */
    private function httpGetWithoutSSL($url)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_USERAGENT, 'EARS-PIDS-Integration/2.0');
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            curl_close($ch);
            
            if ($error || $httpCode !== 200) {
                return null;
            }
            
            return json_decode($response, true);
            
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get all personnel from PIDS API
     * 
     * @return array|null Array of personnel data or null on failure
     */
    public function getAllPersonnel()
    {
        $url = $this->apiBaseUrl . '/personnel?token=' . urlencode($this->apiToken);
        
        Yii::info('Fetching personnel from: ' . $this->apiBaseUrl . '/personnel', __METHOD__);
        
        $response = $this->httpGet($url);
        
        if ($response === null) {
            Yii::error('Failed to fetch personnel from PIDS API', __METHOD__);
            return null;
        }
        
        if (!is_array($response)) {
            Yii::error('API returned non-array data: ' . gettype($response), __METHOD__);
            return null;
        }
        
        // PIDS API returns wrapped response: {"success":true,"count":140,"data":[...]}
        if (isset($response['success']) && $response['success'] && isset($response['data'])) {
            $data = $response['data'];
            Yii::info('Successfully fetched ' . count($data) . ' personnel records', __METHOD__);
            return $data;
        }
        
        // Fallback: if it's already an array of personnel
        if (isset($response[0])) {
            Yii::info('Successfully fetched ' . count($response) . ' personnel records (unwrapped)', __METHOD__);
            return $response;
        }
        
        Yii::error('Unexpected API response structure', __METHOD__);
        return null;
    }

    /**
     * Get department name from nested structure in personnel data
     * PIDS API returns: personnel.division.department.name
     * 
     * @param array $person Personnel data with nested division/department
     * @return string|null Department name or null if not found
     */
    public function getDepartmentName($person)
    {
        // Check nested structure: person -> division -> department -> name
        if (isset($person['division']['department']['name'])) {
            return $person['division']['department']['name'];
        }
        
        // Try alternate field names
        if (isset($person['division']['department']['department_name'])) {
            return $person['division']['department']['department_name'];
        }
        
        // If no nested department, return null
        return null;
    }

    /**
     * Get division name from nested structure in personnel data
     * 
     * @param array $person Personnel data with nested division
     * @return string|null Division name or null if not found
     */
    public function getDivisionName($person)
    {
        if (isset($person['division']['name'])) {
            return $person['division']['name'];
        }
        
        if (isset($person['division']['division_name'])) {
            return $person['division']['division_name'];
        }
        
        return null;
    }

    /**
     * Get personnel by email/username
     * 
     * @param string $identifier Email or username
     * @return array|null Personnel data or null if not found
     */
    public function getPersonnelByIdentifier($identifier)
    {
        $allPersonnel = $this->getAllPersonnel();
        
        if (!$allPersonnel) {
            return null;
        }

        foreach ($allPersonnel as $personnel) {
            // Check if identifier matches email or username
            if (
                (isset($personnel['email']) && strtolower($personnel['email']) === strtolower($identifier)) ||
                (isset($personnel['username']) && strtolower($personnel['username']) === strtolower($identifier))
            ) {
                return $personnel;
            }
        }

        return null;
    }

    /**
     * Authenticate user against PIDS API
     * 
     * @param string $username Username or email
     * @param string $password Password (if needed for future implementation)
     * @return array|null Personnel data if authenticated, null otherwise
     */
    public function authenticateUser($username, $password = null)
    {
        $personnel = $this->getPersonnelByIdentifier($username);
        
        if (!$personnel) {
            return null;
        }

        // For now, we're using single sign-on approach
        // If they exist in PIDS API, they're authenticated
        // You can add additional password verification here if needed
        
        return $personnel;
    }

    /**
     * Get personnel profile picture URL
     * 
     * @param array $personnel Personnel data array
     * @return string|null Profile picture URL or null if not available
     */
    public function getProfilePictureUrl($personnel)
    {
        if (isset($personnel['profile_picture']) && !empty($personnel['profile_picture'])) {
            // If it's a full URL
            if (filter_var($personnel['profile_picture'], FILTER_VALIDATE_URL)) {
                return $personnel['profile_picture'];
            }
            
            // If it's a relative path, prepend the base URL
            return rtrim($this->apiBaseUrl, '/api') . '/' . ltrim($personnel['profile_picture'], '/');
        }

        return null;
    }

    /**
     * Check if user has access to EARS
     * This can be customized based on your access control requirements
     * 
     * @param array $personnel Personnel data
     * @return bool True if user has access, false otherwise
     */
    public function hasEarsAccess($personnel)
    {
        // Check against user table
        if (class_exists('\common\models\User')) {
            $pidsId = $personnel['id'] ?? null;
            if ($pidsId) {
                return \common\models\User::hasEarsAccess($pidsId);
            }
        }
        
        // Fallback: no access if User model not found
        return false;
    }

    /**
     * Sync personnel data to local user table if needed
     * 
     * @param array $personnel Personnel data from API
     * @return bool True on success, false on failure
     */
    public function syncPersonnelToUser($personnel)
    {
        // This method can be used to sync PIDS personnel data
        // to your local user table for faster access
        // Implementation depends on your User model structure
        
        return true;
    }
}
