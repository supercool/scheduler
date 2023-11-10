<?php

namespace supercool\scheduler\jobs;

/**
 * ReSaveElement Job
 *
 * This Job will re-save the given element.
 *
 * If that element is a Matrix or SuperTable block it will also save the owner
 *
 * @package   Scheduler
 * @copyright Copyright (c) 2018, Supercool Ltd
 * @link      https://github.com/supercool/Scheduler
 */

use Craft;

use supercool\scheduler\jobs\BaseSchedulerJob;

class SchedulerReSaveElementJob extends BaseSchedulerJob
{

	// Public Methods
	// =========================================================================

	/**
	 * @inheritDoc IScheduler_Job::run()
	 *
	 * @return bool
	 */
	public function run(): bool
	{
		// Get the model
		$job = $this->model;

		// Get the elementId from the model settings
		$elementId = $job->settings['elementId'];

		try
		{
			// Get the element model
			$element = Craft::$app->elements->getElementById((int) $elementId);

			// Check there was one - if not then do nothing and return true so it is removed from the queue
			if (!$element) {
				return true; 
			}

			// Re-save the element using the Element Types save method
			// Now save it
			if ( Craft::$app->elements->saveElement($element, false) )
			{

				// Check if the element has an owner (MatrixBlock, SuperTableBlockElement)
				// and if so, then save that too
				if ($element instanceof \craft\elements\MatrixBlock || $element instanceof \verbb\supertable\elements\SuperTableBlockElement)
				{
					$owner = $element->getOwner();
					if ($owner)
					{
						Craft::$app->elements->saveElement($owner, false);
					}
				}

				return true;
			}
			else
			{
				return false;
			}
		}
		catch (\Exception $e)
		{
			Craft::error(Craft::t('scheduler', 'An exception was thrown while trying to save the element with the ID “'.$elementId.'”: '.$e->getMessage()));
			return false;
		}

		return false;
	}

}
