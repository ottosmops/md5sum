<?php

/**
 * Md5sum create and verify md5sum files
 *
 * PHP version 5.6
 *
 * @category File-Verification
 * @package  Md5sum
 * @author   andreas kraenzle <kraenzle@k-r.ch>
 * @license  https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link     https://github.com/ottosmops/md5sum
 */

namespace Ottosmops\Md5sum;

use Ottosmops\Md5sum\Exceptions\FileNotFound;
use Ottosmops\Md5sum\Exceptions\DirNotFound;
use Ottosmops\Md5sum\Exceptions\SeparatorNotFound;

/**
 *  Md5sum
 *
 * @category Class
 * @package  Md5sum
 * @author   andreas kraenzle <kraenzle@k-r.ch>
 */
class Md5sum
{

    public $md5sums;

    public $dir;

    public $lines = [];

    public $messages = [];

    /**
     * createChecksums
     * @param  string $dir     path to dir
     * @param  string $md5sums optional path to a md5sum file, it must be relative to dir
     * @return int             line count
     */
    public function createMd5sums($dir, $md5sums = '', $deep = true)
    {
        $this->dir = realpath($dir) .'/';

        if (!is_dir($this->dir)) {
            throw new DirNotFound("could not find directory {$dir}");
        }

        $this->md5sums = ($md5sums == '') ? $this->dir . 'md5sums' : $this->dir . $md5sums;

        $md5sums_pathinfo = pathinfo($this->md5sums);
        $md5sums_dir = $md5sums_pathinfo['dirname'];

        $lcn = 0;
        foreach ($this->filelist($dir, $deep) as $file) {
            if (is_file($file) && ($file != $this->md5sums)) {
                $lcn ++;
                $lines[] =  md5_file($file) . '  ' . $this->trimPath($this->getRelativePath($md5sums_dir, $file));
            }
        }
        file_put_contents($this->md5sums, implode("\n", $lines));

        return $lcn;
    }

    public function verifyMd5sums($md5sums)
    {
        if (!is_file($md5sums) && !is_readable($md5sums)) {
            throw new FileNotFound("could not find or open file {$md5sums}");
        }
        $this->md5sums = realpath($md5sums);
        $this->readLines();
        return $this->checkLines();
    }

    private function readLines()
    {
        $fh = fopen($this->md5sums, "r");
        $sep = '';
        $lc = 0;
        while (!feof($fh)) {
            $lc++;
            $line = fgets($fh);
            if (empty($line)) {
                continue;
            }

            $sep = $this->detectSeparator($line);

            $this->lines[$lc] = explode($sep, trim($line));
            $this->lines[$lc]['binary'] = $sep === ' *' ? true : false;
        }
        fclose($fh);
    }

    private function checkLines()
    {
        $cwd_old = getcwd();
        chdir(pathinfo($this->md5sums)['dirname']);

        foreach ($this->lines as $k => $line) {
            if (!is_file($line[1])) {
                $this->messages[$k] = sprintf('error line %d: could not find %s', $k, $line[1]);
                continue;
            }
            if (!$this->checkLine($line)) {
                $this->messages[$k] = sprintf('error line %d: could not verify line %s  %s', $k, $line[0], $line[1]);
            }
        }

        chdir($cwd_old);

        return count($this->messages) ? false : true;
    }

    private function checkLine($line)
    {
        $file = $line[1];
        return hash_file('md5', $file) == $line[0];
    }

    private function trimPath($path)
    {
        return str_replace('./', '', $path);
    }

    /**
     * [detectSeparator description]
     * @param  string $line
     * @return string       '  ' or ' *' or false
     */
    private function detectSeparator($line)
    {
        if (preg_match('|^[a-f0-9]{32}  (/)?([^/\0]+(/)?)+$|', $line)) {
            $sep = '  ';
        } elseif (preg_match('|^[a-f0-9]{32} \*(/)?([^/\0]+(/)?)+$|', $line)) {
            $sep = ' *';
        } else {
            throw new SeparatorNotFound("could not detect an apropriate sepataror $line");
        }
        return $sep;
    }

    private function filelist($dir, $deep = true)
    {
        $array = [];
        if ($deep) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $filename) {
                if (is_file($filename)) {
                    $array[] = realpath("$filename");
                }
            }
        } else {
            foreach (glob($dir.'/*') as $filename) {
                if (is_file($filename)) {
                    $array[] = realpath("$filename");
                }
            }
        }
        return $array;
    }

    /**
     * [getRelativePath description]
     * from http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php
     * @param  string $from absolute path
     * @param  string $to   absolute path
     * @return string       relativ path
     */
    private function getRelativePath($from, $to)
    {
        // some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
        $from = str_replace('\\', '/', $from);
        $to   = str_replace('\\', '/', $to);

        $from     = explode('/', $from);
        $to       = explode('/', $to);
        $relPath  = $to;

        foreach ($from as $depth => $dir) {
            // find first non-matching dir
            if ($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if ($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }
        return implode('/', $relPath);
    }
}
