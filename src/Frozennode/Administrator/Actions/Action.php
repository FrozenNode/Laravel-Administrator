<?php
namespace Frozennode\Administrator\Actions;

use Frozennode\Administrator\Config\ConfigInterface;
use Frozennode\Administrator\Traits\OptionableTrait;

class Action {

	use OptionableTrait;

	/**
	 * The config instance
	 *
	 * @var \Frozennode\Administrator\Config\ConfigInterface
	 */
	protected $config;

	/**
	 * The default configuration options
	 *
	 * @var array
	 */
	protected $defaultOptions = array(
		'title' => 'Custom Action',
		'has_permission' => true,
		'confirmation' => false,
		'messages' => array(
			'active' => 'Just a moment...',
			'success' => 'Success!',
			'error' => 'There was an error performing this action',
		),
	);

	/**
	 * The base rules that all fields need to pass
	 *
	 * @var array
	 */
	protected $rules = array(
		'title' => 'string_or_callable',
		'confirmation' => 'string_or_callable',
		'messages' => 'array|array_with_all_or_none:active,success,error',
		'action' => 'required|callable',
	);

	/**
	 * Create a new action Factory instance
	 *
	 * @param \Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param array												$options
	 */
	public function __construct(ConfigInterface $config, array $options)
	{
		$this->config = $config;
		$this->options = $options;
	}

	/**
	 * Builds the necessary fields on the object
	 *
	 * @param array		$options
	 *
	 * @return array
	 */
	public function buildOptions($options)
	{
		//build the string or callable values for title and confirmation
		$this->buildStringOrCallable($options, array('confirmation', 'title'));

		//build the string or callable values for the messages
		$messages = array_get($options, 'messages', []);
		$this->buildStringOrCallable($messages, array('active', 'success', 'error'));
		$options['messages'] = $messages;

		//override the supplied options
		return $options;
	}

	/**
	 * Sets up the values of all the options that can be either strings or closures
	 *
	 * @param array		$options	//the passed-by-reference array on which to do the transformation
	 * @param array		$keys		//the keys to check
	 *
	 * @return void
	 */
	public function buildStringOrCallable(array &$options, array $keys)
	{
		$model = $this->config->getDataModel();

		//iterate over the keys
		foreach ($keys as $key)
		{
			//check if the key's value was supplied
			$suppliedValue = array_get($options, $key);

			//if it's a string, simply set it
			if (is_string($suppliedValue))
			{
				$options[$key] = $suppliedValue;
			}
			//if it's callable pass it the current model and run it
			else if (is_callable($suppliedValue))
			{
				$options[$key] = $suppliedValue($model);
			}
		}
	}

	/**
	 * Performs the callback of the action and returns its result
	 *
	 * @param mixed		$data
	 *
	 * @return array
	 */
	public function perform(&$data)
	{
		$action = $this->getOption('action');

		return $action($data);
	}

}