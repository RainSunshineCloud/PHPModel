<?php
class ErrorController extends Yaf\Controller_Abstract
{
	public function errorAction()
	{
		$exception = $this->getRequest()->getException();
		printf('%s:%s=>%s',$exception->getFile(),$exception->getLine(),$exception->getMessage());
		return false;
	}

}