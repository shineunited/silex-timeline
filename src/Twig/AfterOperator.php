<?php

namespace ShineUnited\Silex\Timeline\Twig;


class AfterOperator extends \Twig_Node_Expression_Unary {

	public function compile(\Twig_Compiler $compiler) {
		$compiler
			->raw('call_user_func([$context[\'timeline\'], \'compareEpochs\'], \'now\', ')
			->subcompile($this->getNode('node'))
			->raw(', \'isAfter\')')
		;
	}

	public function operator(\Twig_Compiler $compiler) {
		return $compiler->raw('');
	}
}
