<?php

namespace App\Helpers;

use CloudConvert\Api;

class CloudConvertHelper
{
    public function __construct()
    {
        $api_key = config('cloudconvert.api_key');
        $this->api = new Api($api_key);
    }

    /**
     * Convert file to PDF.
     *
     * @param string $file_input
     * @param string $mime_type
     *
     * @return string
     */
    public static function fileToPDF($file_input, $mime_type)
    {
        $output = $this->process('convert', 'docx', $file_input);

        dd($output, $mime_type);

        return $output;
    }

    /**
     * Encrypt PDF.
     *
     * @param string $file_input
     *
     * @return string
     */
    public static function encryptPDF($file_input)
    {
        $owner_password = UtilsHelper::generateRandomToken();

        $settings = [
            'converteroptions' => [
                'encrypt' => true,
                'encrypt_user_password' => null,
                'encrypt_owner_password' => $owner_password,
                'encrypt_allow_accessibility' => false,
                'encrypt_allow_extract' => false,
                'encrypt_allow_print' => 'none',
                'encrypt_allow_modify' => 'none',
                'encrypt_mode' => 'aes',
            ],
        ];

        $output = $this->process('protect', 'pdf', $file_input, $settings);

        dd($output);

        return $output;
    }

    /**
     * Helper function for executing process.
     *
     * @param string     $mode
     * @param string     $inputFormat
     * @param string     $inputFile
     * @param null|array $additionalSettings
     *
     * @return mixed
     */
    protected function process($mode, $inputFormat, $inputFile, $additionalSettings = null)
    {
        $process = $this->api->createProcess([
            'mode' => $mode,
            'inputformat' => $inputFormat,
            'outputformat' => 'pdf',
            'timeout' => config('cloudconvert.timeout'),
        ]);

        $settings = [
            'mode' => $mode,
            'input' => 'upload',
            'file' => $inputFile,
        ];

        if ($additionalSettings) {
            $settings = array_merge($settings, $additionalSettings);
        }

        return $process->start($settings)->wait()->download();
    }
}
