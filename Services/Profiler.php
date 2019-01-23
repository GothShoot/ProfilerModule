<?php

namespace Module\ProfilerModule\Services;

use Module\CoreModule\BaseClass\Singleton;

use Module\CoreModule\Services\ConfigLoader;

class Profiler extends Singleton
{
    private $token;

    private $time = [];
    private $error = [];
    private $alert = [];
    private $dump = [];
    private $modules = [];
    private $page = ['time', 'error', 'alert', 'dump'];

    public function __construct()
    {
        $this->modules = ConfigLoader::getInstance()->getModule();
    }

    public function profilerPage()
    {
        $profil = json_decode(file_get_contents(ROOT_DIR.'/Var/Cache/Profile/'. $_GET['token'] .'.json'), true);

        $this->token = $profil['token'];
        $this->time = $profil['time'];
        $this->error = $profil['error'];
        $this->alert = $profil['alert'];
        $this->dump = $profil['dump'];

        $this->printProfile();
    }

    public function printProfile()
    {
        echo 'token: '.$this->token.'<br />';
        echo '<a href="'. explode('?', $_SERVER['REQUEST_URI'])[0] .'">back</a> ';
        foreach($this->page as $page){
            echo '<a href="?token='. $this->token .'&page='. $page .'">'. $page .'</a> ';
        }
        echo '<hr />';
        echo '<table>';
        switch($_GET['page']){
            case 'time':
                echo '<tr><td>object</td><td>execution time</td></tr>';
                foreach($this->time as $time){
                    echo '<tr><td>'.$time['name'].'</td><td>'.$this->formatTime($time['ExecutionTime']).'</td></tr>';
                }
            break;
            case 'error' :
                echo '<tr><td>type</td><td>value</td></tr>';
                foreach($this->error as $error){
                    echo '<tr><td>'.$error['error'].'</td></tr>';
                }
            break;
            case 'alert' :
                echo '<tr><td>type</td><td>value</td></tr>';
                foreach($this->alert as $alert){
                    echo '<tr><td>'.$alert['alert'].'</td></tr>';
                }
            break;
            case 'dump':
                echo '<tr><td>type</td><td>value</td></tr>';
                foreach($this->dump as $dump){
                    echo '<tr><td>'.$dump['name'].'</td><td>'.gettype($dump['value']).'</td><td>'.$dump['value'].'</td></tr>';
                }
            break;
            case 'modules':
                echo '<pre>';
                var_dump($this->modules);
                echo '</pre>';
            break;
        }
        echo '</table>';
    }

    public function profileBar()
    {
        echo '
            <hr>
            <style>
                .profileBar { padding: 3px; background-color: #6495ED; }
                .profileBar a { padding: 3px; color: #FFF; }
                .time-green { background-color: #32CD32; }
                .time-red { background-color: #FF4500; }
                .time-orange { background-color: #FFA500; }
            </style>
            <div class="profileBar">
            <a class="time-'.($this->time[count($this->time)-1]['ExecutionTime'] < 150 ? 'green':'red' ).'" href="?token='. $this->token .'&page=time">'. $this->formatTime($this->time[count($this->time)-1]['ExecutionTime']) .'</a>
            <a class="time-'.(count($this->error) == 0 ? 'green':'red' ).'" href="?token='. $this->token .'&page=error">erreur: '. count($this->error) .'</a>
            <a class="time-'.(count($this->alert) == 0 ? 'green':'orange' ).'" href="?token='. $this->token .'&page=alert">alert: '. count($this->alert) .'</a>
            <a class="time-'.(count($this->dump) == 0 ? 'green':'orange' ).'" href="?token='. $this->token .'&page=dump">dump: '. count($this->dump) .'</a>
            <a class="time-green" href="?token='. $this->token .'&page=modules">modules: '. count($this->modules) .'</a>
            </div>
        ';
    }

    private function formatTime($time)
    {
        return number_format($time , 2 , "ms" , "s" );
    }

    public function __destruct()
    {
        $profil = ['token'=>$this->token, 'time'=>$this->time, 'error'=>$this->error, 'alert'=>$this->alert, 'dump'=>$this->dump];
        if( !file_exists( (ROOT_DIR.'/Var/Cache/Profile') ) ){mkdir(ROOT_DIR.'/Var/Cache/Profile', 0775, true);}
        file_put_contents(ROOT_DIR.'/Var/Cache/Profile/'. $this->token .'.json', json_encode($profil));
    }

// Getters and Setters
    public function getTime(?string $id = null):array
    {
        if(!$id)  {return $this->time;}
        return $this->time[$id];
    }

    public function setTime(array $data)
    {
        if(!isset($this->token)) {$this->token = time().'_'.rand(0, 20);}
        $data['id'] = count($this->time);
        $data['ExecutionTime'] =(round( $data['end']-$data['start'], 3)* 1000);
        array_push($this->time, $data);
    }

    public function getError(?string $id = null):array
    {
        if(!$id)  {return $this->error;}
        return $this->error[$id];
    }

    public function setError(array $data)
    {
        $data['id'] = count($this->error);
        array_push($this->error, $data);
    }

    public function getAlert(?string $id = null):array
    {
        if(!$id)  {return $this->alert;}
        return $this->alert[$id];
    }

    public function setAlert(array $data)
    {
        $data['id'] = count($this->alert);
        array_push($this->alert, $data);
    }

    public function getDump(?string $id = null):array
    {
        if(!$id)  {return $this->dump;}
        return $this->dump[$id];
    }

    public function setDump(array $data)
    {
        $data['id'] = count($this->dump);
        array_push($this->dump, $data);
    }

    /**
     * Get the value of modules
     * 
     * @return $modules
     */ 
    public function getModules():array
    {
        return $this->modules;
    }

    /**
     * Set the value of modules
     *
     * @param  $modules
     */ 
    public function setModules(array $modules)
    {
        $this->modules = $modules;
    }
}