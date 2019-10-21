<?php

namespace App\Helpers;

use CloudConvert\Api;

class CloudConvertHelper
{
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
        $file_type = CloudConvertHelper::mime2ext($mime_type);

        return CloudConvertHelper::process('convert', $file_type, $file_input);
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

        return CloudConvertHelper::process('protect', 'pdf', $file_input, $settings);
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
    protected static function process($mode, $inputFormat, $inputFile, $additionalSettings = null)
    {
        $api_key = config('cloudconvert.api_key');
        $api = new Api($api_key);

        $process = $api->createProcess([
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

    /**
     * Mime type to extension.
     *
     * @param string $mime
     *
     * @return string
     */
    protected static function mime2ext($mime)
    {
        $mime_map = [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'image/jpeg' => 'jpeg',
            'image/pjpeg' => 'jpeg',
            'application/pdf' => 'pdf',
            'application/octet-stream' => 'pdf',
            'image/png' => 'png',
            'image/x-png' => 'png',
            'application/powerpoint' => 'ppt',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.ms-office' => 'ppt',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/x-photoshop' => 'psd',
            'image/vnd.adobe.photoshop' => 'psd',
            'text/rtf' => 'rtf',
            'image/svg+xml' => 'svg',
            'image/tiff' => 'tiff',
            'text/plain' => 'txt',
            'application/excel' => 'xl',
            'application/msexcel' => 'xls',
            'application/x-msexcel' => 'xls',
            'application/x-ms-excel' => 'xls',
            'application/x-excel' => 'xls',
            'application/x-dos_ms_excel' => 'xls',
            'application/xls' => 'xls',
            'application/x-xls' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-excel' => 'xlsx',
        ];

        return true === isset($mime_map[$mime]) ? $mime_map[$mime] : false;
    }
}
