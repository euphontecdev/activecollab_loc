<?php

/**
 * Proxy for easy compressing files
 *  - if ZipArchive is present, then it is used, if not failsafe option
 *
 */
class SimpleZipArchiver extends AngieObject {

  /**
   * To use old functions in case ZipArchive does not exists
   *
   * @var boolean
   */
  var $fail_safe_method = false;

  /**
   * Zip File
   *
   * @var mixed
   */
  var $zip_file;

  /**
   * Zip filename
   *
   * @var string
   */
  var $zip_filename;

  /**
   * Function that initializes SimpleZipArchiver
   *
   * @param void
   * @return boolean
   */
  function __construct() {
    $this->fail_safe_method = !class_exists('ZipArchive');
    if (!$this->fail_safe_method) {
      $this->zip_file = new ZipArchive();
    } else {
      require_once(ANGIE_PATH.'/classes/Zip.class.php');
      $this->zip_file = new zipfile();
    } // if

    return (boolean) extension_loaded('zlib');
  } // __construct

  /**
   * Function that opens zip file for writing
   *
   * @param string $zip_filename
   */
  function open($zip_filename) {
    $this->zip_filename = $zip_filename;

    if (!$this->fail_safe_method) {
      return $this->zip_file->open($zip_filename, ZIPARCHIVE::OVERWRITE);
    } else {
      if (is_file($zip_filename)) {
        return file_is_writable($zip_filename);
      } else {
        return folder_is_writable(dirname($zip_filename));
      } // if
    } // if
  } // open

  /**
   * Add file to archive
   *
   * @param string $file_to_add
   * @param string $dest_filename
   */
  function addFile($file_to_add, $dest_filename) {
    if (!$this->fail_safe_method) {
      return $this->zip_file->addFile($file_to_add, $dest_filename);
    } else {
      return $this->zip_file->addFile(file_get_contents($file_to_add), $dest_filename);
    } // if
  } // addFile

  /**
   * Closes that file
   *
   * @param void
   * @return boolean
   */
  function close() {
    if (!$this->fail_safe_method) {
      return $this->zip_file->close();
    } else {
      return $this->zip_file->output($this->zip_filename);
    } // if
  } // close

} // SimpleZipArchiver

?>