<?php
namespace app\command;
 
use think\console\Command;
use think\console\Input;
use think\console\Output;
class Shoulu extends \think\console\Command
    {
        protected function configure()
        {
            $this->setName('shoulu')->setDescription('this is a shoulu');
        }
        protected function execute(Input $input, Output $output)
        { 
          $task=model('common/contrab/Embody');
		  $result=$task->shoulustart();
		  if($result){
			  $output->writeln('success');
		  }else{
			  $output->writeln('error');
		  }
          
        }
    }