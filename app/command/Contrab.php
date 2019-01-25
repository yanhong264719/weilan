<?php
namespace app\command;
 
use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\admin\controller\Publish;

class Contrab extends \think\console\Command
    {
        protected function configure()
        {
            $this->setName('contrab')->setDescription('this is a mini contrab manager tool!');
        }
        protected function execute(Input $input, Output $output)
        {
          $task=model('common/contrab/Order');
		  $result=$task->timestart();
		  if($result){
			  $output->writeln('success');
		  }else{
			  $output->writeln('error');
		  }
          
        }
    }