<?php
namespace Package\Raxon\Host\Trait;

use Raxon\Node\Module\Node;

use Exception;

use Raxon\Exception\FileAppendException;
use Raxon\Exception\ObjectException;

trait System {

    /**
     * @throws ObjectException
     * @throws FileAppendException
     * @throws Exception
     */
    public function create(object $flags, object $options): void
    {             
        if(!property_exists($options, 'domain')){
            throw new Exception('Host create error: domain is required');
        }
        $explode = explode('.', $options->domain);
        if(count($explode) < 2){
            throw new Exception('Host create error: domain must contain at least a name and an extension');
        }
        if(count($explode) > 2){
            throw new Exception('Host create error: only domain with at least a name and an extension');
        }
        $force = $options->force ?? false;
        $options->name = ucfirst($explode[0]) . '.' . ucfirst($explode[1]);
        $options->extension = $explode[1];  
        $options->domain = $explode[0];
        $object = $this->object();
        $node = new Node($object);
        $class = 'System.Host';
        $record = (object) [
            'name' => $options->name,
            'domain' => $options->domain,
            'extension' => $options->extension,
            'url' => (object) [
                'development' => $options->domain . '.local/',
                'production' => $options->domain . '.' . $options->extension,
            ]
        ];
        $exist = $node->record($class, $node->role_system(), [
            'where' => [
                [
                    'value' => $record->name,
                    'attribute' => 'name',
                    'operator' => '===',
                ]
            ]
        ]);
        $response = [];
        if($exist && $force === false){
            throw new Exception('Host create error: host already exists, use option -force to overwrite');
        }
        elseif(
            $exist &&
            is_array($exist) &&
            array_key_exists('node', $exist) &&
            property_exists($exist['node'], 'uuid')
        ){
            $record->uuid = $exist['node']->uuid;
            $response = $node->put($class, $node->role_system(), $record);
        }
        elseif(!$exist) {
            $response = $node->create($class, $node->role_system(), $record);
        }
        $mapper_options = (object) [
            'source' => $record->url->development,
            'destination' => $record->url->production,
            'force' => $force
        ];        
        $this->create_mapper($flags, $mapper_options);
    }

    /**
     * @throws ObjectException
     * @throws FileAppendException
     * @throws Exception
     */
    public function create_mapper(object $flags, object $options): void
    {             
        if(!property_exists($options, 'source')){
            throw new Exception('Host mapper create error: source must be .local address');
        }
        if(!property_exists($options, 'destination')){
            throw new Exception('Host mapper create error: destination must be public address');
        }
        $force = $options->force ?? false;        
        $object = $this->object();
        $node = new Node($object);
        $class = 'System.Host.Mapper';
        $record = (object) [
            'source' => $options->source,
            'destination' => $options->destination
        ];
        $exist = $node->record($class, $node->role_system(), [
            'where' => [
                [
                    'value' => $record->source,
                    'attribute' => 'source',
                    'operator' => '===',
                ]
            ]
        ]);
        $response = [];
        if($exist && $force === false){
            throw new Exception('Host.Mapper create error: host.map already exists, use option -force to overwrite');
        }
        elseif(
            $exist &&
            is_array($exist) &&
            array_key_exists('node', $exist) &&
            property_exists($exist['node'], 'uuid')
        ){
            $record->uuid = $exist['node']->uuid;
            $response = $node->put($class, $node->role_system(), $record);
        } elseif(!$exist) {
            $response = $node->create($class, $node->role_system(), $record);
        }    
    }

}