<?php

// 陈旧数据异常
class StaleObjectError extends CException {}

abstract class OptimistLockingActiveRecord extends JggActiveRecord
{

	/**
	 * Overwrite updateByPk method of ActiveRecord
	 * @param mixed $pk
	 * @param array $attributes
	 * @param string $condition
	 * @param array $params
	 * @throws StaleObjectError
	 * @return integer 
	 */
	public function updateByPk($pk, $attributes, $condition='', $params=array())
	{
		$this->applyLockingCondition($condition);
	
		$lockingAttribute = $this->getlockingAttribute();
		$attributes[$lockingAttribute] = $this->$lockingAttribute +1;
		$affectedRows = parent::updateByPk($pk, $attributes, $condition, $params);
		if($affectedRows !=1) {
			$exceptionMessage = "Update row in table " . $this->tableSchema->name . " cause conflict: ";
			if (is_array($pk)) {
				foreach ($pk as $key=>$value) {
					$exceptionMessage .= $key . ":" . $value. "; ";
				}
			} else {
				$exceptionMessage .= "pk:" . $pk . "; ";
			}
			foreach ($attributes as $key=>$value) {
				$exceptionMessage .= $key . ":" . $value . "; ";
			}
			throw new StaleObjectError($exceptionMessage);
		}
		$this->$lockingAttribute = $this->$lockingAttribute +1;
		return $affectedRows;
	}
	
	private function applyLockingCondition(&$condition)
	{
		$lockingAttribute = $this->getlockingAttribute();
		$lockingAttributeValue = $this->$lockingAttribute;
		if(!empty($condition))
			$condition .= ' and ';
		$condition .= "$lockingAttribute = $lockingAttributeValue";
	}
}