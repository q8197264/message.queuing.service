<?php
namespace Cache\Core\Config;

use IteratorAggregate;

/**
 * find config
 * User: Liu xiaoquan
 * Date: 2017/8/4
 * Time: 11:25
 */
class Finder implements IteratorAggregate
{
    const IGNORE_VCS_FILES = 1;
    const IGNORE_DOT_FILES = 2;

    protected $dirs = array();//config dirs

    private static $vcsPatterns = array('.svn', '_svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg');

    public function __construct()
    {
        $this->ignore = static::IGNORE_VCS_FILES | static::IGNORE_DOT_FILES;
    }

    public static function create()
    {
        return new static();
    }

    public function files()
    {
        $this->mode = 1;
        return $this;
    }

    public function name($pattern)
    {
        $this->names[] = $pattern;

        return $this;
    }

    /**
     * Searches files and directories which match defined rules.
     *
     * @param string|array $dirs A directory path or an array of directories
     *
     * @return $this
     *
     * @throws \InvalidArgumentException if one of the directories does not exist
     */
    public function in($dirs)
    {
        $resolvedDirs = array();

        foreach ((array) $dirs as $dir) {

            if (is_dir($dir)) {

                $resolvedDirs[] = $dir;
            } elseif ($glob = glob($dir, (defined('GLOB_BRACE') ? GLOB_BRACE : 0) | GLOB_ONLYDIR)) {
                $resolvedDirs = array_merge($resolvedDirs, $glob);
            } else {
                throw new \InvalidArgumentException(sprintf('The "%s" directory does not exist.', $dir));
            }
        }

        $this->dirs = array_merge($this->dirs, $resolvedDirs);

        return $this;
    }

    public function getIterator()
    {
        //return all of public variable
        $iterator = new \AppendIterator();
        foreach ($this->dirs as $v) {
            $iterator->append($this->searchInDirectory($v));
        }

        return $iterator;
    }


    /**
     * @param $dir
     *
     * @return \Iterator
     */
    private function searchInDirectory($dir)
    {
        array_walk_recursive($this->dirs, function(&$v, $K, $u){
            $v=glob(ltrim($v,'/').'/'.$u[0]);
        }, $this->names);

        $this->dirs = call_user_func_array('array_merge_recursive', $this->dirs);

        $array_a = new \ArrayIterator($this->dirs);
        return $array_a;
    }
}