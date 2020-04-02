<?php
declare(strict_types=1);

namespace App\Template\Extension\Functions;
use CAMOO\Template\Extension\FunctionHelper;

/**
 * Class Foo
 * @author CamooSarl
 */
class Foo extends FunctionHelper
{
	public function getFunctions() : array
	{
		return [$this->add('say_hello', function(){return 'hello world!!';})];
	}
}
