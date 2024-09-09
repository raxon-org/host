<?php
namespace Package\Raxon\Host\Trait;

use Raxon\Config;

use Raxon\Exception\FileWriteException;
use Raxon\Module\Core;
use Raxon\Module\Event;
use Raxon\Module\File;

use Exception;

use Raxon\Exception\FileAppendException;
use Raxon\Exception\ObjectException;

trait Configure {

    /**
     * @throws ObjectException
     * @throws FileAppendException
     * @throws Exception
     */
    public function name_add($options=[]): void
    {
        $options = Core::object($options, Core::OBJECT_ARRAY);
        $object = $this->object();
        if($object->config(Config::POSIX_ID) !== 0){
            $exception = new Exception('Only root can configure host add...');
            Event::trigger($object, 'raxon.host.configure.name.add', [
                'options' => $options,
                'exception' => $exception
            ]);
            throw $exception;
        }
        $ip = '0.0.0.0';
        if(array_key_exists('ip', $options) || !empty($options['ip'])){
            $ip = $options['ip'];
        } else {
            $options['ip'] = $ip;
        }
        $host = false;
        if(array_key_exists('host', $options) || !empty($options['host'])){
            $host = $options['host'];
        }
        if($host === false){
            $exception = new Exception('Host cannot be empty...');
            Event::trigger($object, 'raxon.host.configure.name.add', [
                'options' => $options,
                'exception' => $exception
            ]);
            throw $exception;
        }
        $url = '/etc/hosts';
        if(File::exist($url)){
            $data = explode("\n", File::read($url));
            foreach($data as $nr => $row){
                if(stristr($row, $host) !== false){
                    Event::trigger($object, 'raxon.host.configure.name.add', [
                        'options' => $options,
                        'is_found' => true
                    ]);
                    return;
                }
            }
            $data = $ip . "\t" . $host . "\n";
            $append = File::append($url, $data);
            echo 'Ip: ' . $ip  .' Host: ' . $host . ' added.' . "\n";
            Event::trigger($object, 'raxon.host.configure.name.add', [
                'options' => $options,
                'is_added' => true
            ]);
        }
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     * @throws Exception
     */
    public function name_has($options=[]): bool
    {
        $options = Core::object($options, Core::OBJECT_ARRAY);
        $object = $this->object();
        if ($object->config(Config::POSIX_ID) !== 0) {
            $exception = new Exception('Only root can configure host add...');
            Event::trigger($object, 'raxon.host.configure.name.delete', [
                'options' => $options,
                'exception' => $exception
            ]);
            throw $exception;
        }
        $host = false;
        if (
            array_key_exists('host', $options) ||
            !empty($options['host'])
        ) {
            $host = $options['host'];
        }
        if($host === false){
            $exception = new Exception('Host cannot be empty...');
            Event::trigger($object, 'raxon.host.configure.name.host', [
                'options' => $options,
                'exception' => $exception
            ]);
            throw $exception;
        }
        $url = '/etc/hosts';
        $data = explode("\n", File::read($url));
        foreach ($data as $nr => $row) {
            if (stristr($row, $host) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     * @throws Exception
     */
    public function name_delete($options=[]): void
    {
        $options = Core::object($options, Core::OBJECT_ARRAY);
        $object = $this->object();
        if($object->config(Config::POSIX_ID) !== 0){
            $exception = new Exception('Only root can configure host add...');
            Event::trigger($object, 'raxon.host.configure.name.delete', [
                'options' => $options,
                'exception' => $exception
            ]);
            throw $exception;
        }
        $host = false;
        if(
            array_key_exists('host', $options) ||
            !empty($options['host'])
        ){
            $host = $options['host'];
        }
        if($host === false){
            $exception = new Exception('Host cannot be empty...');
            Event::trigger($object, 'raxon.host.configure.name.delete', [
                'options' => $options,
                'exception' => $exception
            ]);
            throw $exception;
        }
        $url = '/etc/hosts';
        $data = explode("\n", File::read($url));
        $is_delete = false;
        foreach($data as $nr => $row){
            if(stristr($row, $host) !== false){
                unset($data[$nr]);
                $is_delete = true;
            }
        }
        $data = implode("\n", $data);
        $bytes = File::write($url, $data);
        Event::trigger($object, 'cli.configure.host.delete', [
            'options' => $options,
            'is_delete' => $is_delete,
        ]);
        if($is_delete === true){
            echo 'hostname (' . $host . ') deleted...' . PHP_EOL;
        } else {
            echo 'hostname (' . $host . ') not found...' . PHP_EOL;
        }
    }
}