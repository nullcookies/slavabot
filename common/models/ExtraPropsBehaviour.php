<?
namespace common\models;

use yii\base\Behavior;

class ExtraPropsBehaviour extends Behavior
{
    protected $_props = [];

    public function canGetProperty($name, $checkVars = true)
    {
        return TRUE;
    }

    public function canSetProperty($name, $checkVars = true)
    {
        return TRUE;
    }

    public function __get($name)
    {
        return isset($this->_props[$name]) ? $this->_props[$name] : null;
    }

    public function __set($name, $value)
    {
        $this->_props[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->_props);
    }

}
?>