<?php namespace Backend\FormWidgets;

use Carbon\Carbon;
use Backend\Classes\FormField;
use Backend\Classes\FormWidgetBase;

/**
 * Date picker
 * Renders a date picker field.
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class DatePicker extends FormWidgetBase
{
    const TIME_PREFIX = '___time_';

    //
    // Configurable properties
    //

    /**
     * @var string Display format.
     */
    public $format = 'YYYY-MM-DD';

    /**
     * @var bool Display mode: datetime, date, time.
     */
    public $mode = 'datetime';

    /**
     * @var string the minimum/earliest date that can be selected.
     * eg: 2000-01-01
     */
    public $minDate = null;

    /**
     * @var string the maximum/latest date that can be selected.
     * eg: 2020-12-31
     */
    public $maxDate = null;

    //
    // Object properties
    //

    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'datepicker';

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->fillFromConfig([
            'format',
            'mode',
            'minDate',
            'maxDate',
        ]);

        $this->mode = strtolower($this->mode);

        if ($this->minDate !== null) {
            $this->minDate = is_integer($this->minDate)
                ? Carbon::createFromTimestamp($this->minDate)
                : Carbon::parse($this->minDate);
        }

        if ($this->maxDate !== null) {
            $this->maxDate = is_integer($this->maxDate)
                ? Carbon::createFromTimestamp($this->maxDate)
                : Carbon::parse($this->maxDate);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('datepicker');
    }

    /**
     * Prepares the list data
     */
    public function prepareVars()
    {
        $this->vars['name'] = $this->formField->getName();

        $this->vars['timeName'] = self::TIME_PREFIX.$this->formField->getName(false);
        $this->vars['timeValue'] = null;

        if ($value = $this->getLoadValue()) {

            /*
             * Date / Time
             */
            if ($this->mode == 'datetime') {
                if (is_object($value)) {
                    $value = $value->toDateTimeString();
                }

                $dateTime = explode(' ', $value);
                $value = $dateTime[0];
                $this->vars['timeValue'] = isset($dateTime[1]) ? substr($dateTime[1], 0, 5) : '';
            }
            /*
             * Date
             */
            elseif ($this->mode == 'date') {
                if (is_string($value)) {
                    $value = substr($value, 0, 10);
                }
                elseif (is_object($value)) {
                    $value = $value->toDateString();
                }
            }
            elseif ($this->mode == 'time') {
                if (is_object($value)) {
                    $value = $value->toTimeString();
                }
            }

        }

        $this->vars['value'] = $value ?: '';
        $this->vars['field'] = $this->formField;
        $this->vars['format'] = $this->format;
        $this->vars['mode'] = $this->mode;
        $this->vars['minDate'] = $this->minDate;
        $this->vars['maxDate'] = $this->maxDate;
    }

    /**
     * {@inheritDoc}
     */
    protected function loadAssets()
    {
        $this->addCss('vendor/pikaday/css/pikaday.css', 'core');
        $this->addCss('vendor/clockpicker/css/jquery-clockpicker.css', 'core');
        $this->addCss('css/datepicker.css', 'core');
        $this->addJs('js/build-min.js', 'core');
    }

    /**
     * {@inheritDoc}
     */
    public function getSaveValue($value)
    {
        if ($this->formField->disabled) {
            return FormField::NO_SAVE_DATA;
        }

        if (!strlen($value)) {
            return null;
        }

        $timeValue = post(self::TIME_PREFIX . $this->formField->getName(false));
        if ($this->mode == 'datetime' && $timeValue) {
            $value .= ' ' . $timeValue . ':00';
        }
        elseif ($this->mode == 'time') {
            $value = substr($value, 0, 5) . ':00';
        }

        return $value;
    }
}
