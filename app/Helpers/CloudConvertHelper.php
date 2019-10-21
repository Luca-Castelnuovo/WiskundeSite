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
        $process = CloudConvertHelper::process('convert', $file_type, $file_input);

        $file_output = file_get_contents($process->url);
        dd($process, $file_output);

        return CloudConvertHelper::download($process);
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

        $process = CloudConvertHelper::process('protect', 'pdf', $file_input, $settings);

        return CloudConvertHelper::download($process);
    }

    /**
     * Helper function for executing process.
     *
     * @param string     $mode
     * @param string     $inputFormat
     * @param string     $inputFile
     * @param null|array $additionalSettings
     * @param mixed      $input_format
     * @param mixed      $input_file
     * @param mixed      $file_format
     * @param mixed      $file_input
     * @param null|mixed $additional_settings
     *
     * @return mixed
     */
    protected static function process($mode, $file_format, $file_input, $additional_settings = null)
    {
        $api_key = config('cloudconvert.api_key');
        $api = new Api($api_key);

        $process = $api->createProcess([
            'mode' => $mode,
            'inputformat' => $file_format,
            'outputformat' => 'pdf',
            'timeout' => config('cloudconvert.timeout'),
        ]);

        $settings = [
            'mode' => $mode,
            'inputformat' => $file_format,
            'outputformat' => 'pdf',
            'input' => 'raw',
            'file' => $file_input,
            'filename' => 'file.'.$file_format,
        ];

        if ($additional_settings) {
            $settings = array_merge($settings, $additional_settings);
        }

        return $process->start($settings)->wait();
    }

    /**
     * Download converted file into stram.
     *
     * @param mixed $process
     *
     * @return string
     */
    protected static function download($process)
    {
        return file_get_contents($process->url);
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
