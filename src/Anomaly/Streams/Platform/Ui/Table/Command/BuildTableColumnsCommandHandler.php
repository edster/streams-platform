<?php namespace Anomaly\Streams\Platform\Ui\Table\Command;

use Anomaly\Streams\Platform\Contract\ArrayableInterface;
use Anomaly\Streams\Platform\Assignment\AssignmentService;

/**
 * Class BuildTableColumnsCommandHandler
 *
 * @link          http://anomaly.is/streams-platform
 * @author        AnomalyLabs, Inc. <hello@anomaly.is>
 * @author        Ryan Thompson <ryan@anomaly.is>
 * @package       Anomaly\Streams\Platform\Ui\Table\Command
 */
class BuildTableColumnsCommandHandler
{

    /**
     * The assignment service object.
     *
     * @var \Anomaly\Streams\Platform\Assignment\AssignmentService
     */
    protected $service;

    /**
     * Create a new BuildTableColumnsCommandHandler instance.
     *
     * @param AssignmentService $service
     */
    function __construct(AssignmentService $service)
    {
        $this->service = $service;
    }

    /**
     * Handle the command.
     *
     * @param BuildTableColumnsCommand $command
     * @return array
     */
    public function handle(BuildTableColumnsCommand $command)
    {
        $ui    = $command->getUi();
        $entry = $command->getEntry();

        $columns = [];

        foreach ($ui->getColumns() as $column) {

            /**
             * If the column is a string it means
             * they just passed in the field slug.
             */
            if (is_string($column)) {

                $column = ['field' => $column];

            }

            // Evaluate the column.
            $column = $this->evaluate($column, $ui, $entry);

            // Build out our required data.
            $value = $this->getValue($column, $entry);

            $columns[] = compact('value');

        }

        return $columns;
    }

    /**
     * Evaluate each array item for closures.
     * Merge in entry data at this point too.
     *
     * @param $column
     * @param $ui
     * @param $entry
     * @return mixed|null
     */
    protected function evaluate($column, $ui, $entry)
    {
        $column = evaluate($column, [$ui, $entry]);

        /**
         * In addition to evaluating we need
         * to merge in entry data as best we can.
         */
        foreach ($column as &$value) {

            if (is_string($value) and str_contains($value, '{')) {

                if ($entry instanceof ArrayableInterface) {

                    $value = merge($value, $entry->toArray());

                } else {

                    $value = merge($value, (array)$entry);

                }

            }

        }

        return $column;
    }

    /**
     * Get the value.
     *
     * @param $column
     * @param $entry
     * @return mixed
     */
    protected function getValue($column, $entry)
    {
        if (isset($column['value'])) {

            $value = $column['value'];

        } else {

            $value = $this->getValueFromField($column, $entry);

        }

        return $value;
    }

    /**
     * Get the value from a streams field.
     *
     * @param $column
     * @param $entry
     * @return mixed
     */
    protected function getValueFromField($column, $entry)
    {
        $assignment = $entry->getStream()->assignments->findByFieldSlug($column['field']);

        if ($assignment) {

            return $this->service->buildFieldType($assignment, $entry)->getValue();

        }

        return $column['field'];
    }

}
 