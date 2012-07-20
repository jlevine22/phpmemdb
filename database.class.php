<?php
class Database
{
	protected $_data = array();
	
	public function __construct()
	{
		$this->_data[0] = array();
	}
	
	public function execute($commandString = "")
	{
		$response = null;
		$parameters = explode(" ", $commandString);
		$command = strtoupper($parameters[0]);
		$key = isset($parameters[1]) ? $parameters[1] : null;
		$value = isset($parameters[2]) ? implode(' ', array_slice($parameters, 2)) : null;
		switch ($command) {
			case 'SET':
			case 'GET':
			case 'UNSET':
				$response = call_user_func_array(array($this, '_' . strtolower($command) . 'Value'), array($key, $value));
				break;
			case 'BEGIN':
				$this->_data[sizeof($this->_data)] = array();
				break;
			case 'COMMIT':
				$this->_commitTransaction();
				break;
			case 'ROLLBACK':
				$this->_rollbackTransaction();
				break;
			case 'NUMEQUALTO':
				$response = $this->_numEqualTo($parameters[1]);
				break;
			case 'DEBUG':
				print_r($this->_data);
				break;
			default:
				throw new Exception("Unknown command");
		}
		return $response;
	}
	
	protected function _commitTransaction()
	{
		$transactionCount = sizeof($this->_data);
		if ($transactionCount > 1) {
			foreach ($this->_data[$transactionCount - 1] as $key => $value) {
				$this->_data[$transactionCount - 2][$key] = $value;
			}
			unset($this->_data[$transactionCount - 1]);
		}
		else {
			throw new Exception("Nothing to commit");
		}
	}
	
	protected function _rollbackTransaction()
	{
		$transactionCount = sizeof($this->_data);
		if ($transactionCount > 1) {
			unset($this->_data[$transactionCount - 1]);
		}
		else {
			throw new Exception("Invalid rollback");
		}
	}
	
	protected function _setValue($key, $value)
	{
		$this->_data[sizeof($this->_data) - 1][$key] = $value;
		return null;
	}
	
	protected function _getValue($key, $transactionOffset = 0)
	{
		$transactionIndex = sizeof($this->_data) - 1 - $transactionOffset;
		if ($transactionIndex < 0 OR $transactionIndex >= sizeof($this->_data)) {
			return 'NULL';
		}
		if (isset($this->_data[$transactionIndex][$key])) {
			return $this->_data[$transactionIndex][$key];
		}
		else {
			return $this->_getValue($key, $transactionOffset+1);
		}
	}
	
	protected function _unsetValue($key)
	{
		unset($this->_data[sizeof($this->_data) - 1][$key]);
		return null;
	}
	
	protected function _numEqualTo($value)
	{
		$numEqualTo = 0;
		$checkedKeys = array();
		for ($i=0,$x=sizeof($this->_data); $i < $x; $i++) {
			foreach (array_keys($this->_data[$i]) as $key) {
				if (isset($checkedKeys[$key])) {
					continue;
				}
				$checkedKeys[$key] = 1;
				if ($this->_getValue($key) == $value) {
					$numEqualTo++;
				}
			}
		}
		return $numEqualTo;
	}
}