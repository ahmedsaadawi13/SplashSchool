<?php
// FILE: /app/helpers/Upload.php

class Upload {
    protected $file;
    protected $errors = [];
    protected $uploadPath;
    protected $allowedTypes = [];
    protected $maxSize = MAX_UPLOAD_SIZE;

    public function __construct($file, $uploadPath = null) {
        $this->file = $file;
        $this->uploadPath = $uploadPath ?: UPLOADS;
    }

    public function setAllowedTypes($types) {
        $this->allowedTypes = $types;
        return $this;
    }

    public function setMaxSize($size) {
        $this->maxSize = $size;
        return $this;
    }

    public function validate() {
        // Check if file was uploaded
        if (!isset($this->file['tmp_name']) || empty($this->file['tmp_name'])) {
            $this->errors[] = 'No file uploaded.';
            return false;
        }

        // Check for upload errors
        if ($this->file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($this->file['error']);
            return false;
        }

        // Check file size
        if ($this->file['size'] > $this->maxSize) {
            $this->errors[] = 'File size exceeds maximum allowed size (' . $this->formatBytes($this->maxSize) . ').';
            return false;
        }

        // Check file type
        if (!empty($this->allowedTypes)) {
            $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $this->allowedTypes)) {
                $this->errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $this->allowedTypes);
                return false;
            }
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $this->file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        if (!in_array($mimeType, $allowedMimes)) {
            $this->errors[] = 'Invalid file type.';
            return false;
        }

        return true;
    }

    public function upload($subfolder = '') {
        if (!$this->validate()) {
            return false;
        }

        // Create upload directory if it doesn't exist
        $targetPath = $this->uploadPath . '/' . $subfolder;
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        // Generate unique filename
        $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
        $filename = $this->generateUniqueFilename() . '.' . $extension;
        $targetFile = $targetPath . '/' . $filename;

        // Move uploaded file
        if (move_uploaded_file($this->file['tmp_name'], $targetFile)) {
            return [
                'filename' => $filename,
                'path' => $targetFile,
                'size' => $this->file['size'],
                'original_name' => $this->file['name'],
                'mime_type' => $this->file['type']
            ];
        }

        $this->errors[] = 'Failed to move uploaded file.';
        return false;
    }

    protected function generateUniqueFilename() {
        return uniqid() . '_' . time() . '_' . bin2hex(random_bytes(8));
    }

    protected function getUploadErrorMessage($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in HTML form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];

        return isset($errors[$errorCode]) ? $errors[$errorCode] : 'Unknown upload error';
    }

    protected function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getFirstError() {
        return !empty($this->errors) ? $this->errors[0] : null;
    }

    public static function delete($filePath) {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
}
