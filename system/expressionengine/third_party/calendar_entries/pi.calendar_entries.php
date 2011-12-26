<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name' => 'Calendar Entries',
	'pi_version' => '1.0',
	'pi_author' => 'GDmac',
	'pi_author_url' => '',
	'pi_description' => 'Output ExpressionEngine entries in a Codeigniter Calendar.',
	'pi_usage' => 'see included readme',
);

class Calendar_entries
{
	/* settings */
	protected $date_type = 'long';
	public $return;
	protected $config = array(
		   'start_day'  => 'sunday', // Sets the day of the week the calendar should start on.
		   'month_type' => 'long',   // Month name in the header. long = January, short = Jan.
		   'day_type'   => 'long',   // Weekday names to use in the column headers. long = Sunday, short = Sun, abr = Su.
		   'show_next_prev' => true, // Display links to next/previous months.
		   'next_prev_url'  => '',   // Sets the basepath used in the next/previous calendar links.
		// 'template'   => '',       // A string containing your calendar template. See the template section below.
		// 'local_time' => time(),   // A Unix timestamp corresponding to the current time.
	);


	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->tagdata = $this->EE->TMPL->tagdata;
	}
	

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	public function calendarize()
	{
		$year = $this->EE->TMPL->fetch_param('year', false);
		$month = $this->EE->TMPL->fetch_param('month', false);

		foreach($this->config as $key => &$value)
		{
			$value = $this->EE->TMPL->fetch_param($key, $value);
		}

		$data = array();
		
		foreach( $this->EE->TMPL->var_pair as $key => $params)
		{
			if (strncmp($key, 'item', 4) == 0)
			{
				$day     = date('d', $params['day']);
				$tmp     = preg_match("/{".preg_quote($key)."}(.*?){\/item}/s", $this->tagdata, $matches);
				$content = $matches[1];
				$data[(int)$day] = (isset($data[(int)$day]) ? $data[(int)$day] : '') . $content;
			}

			if (strncmp($key, 'calendar_template', 17) == 0)
			{
				$tmp = preg_match("/{".preg_quote($key)."}(.*?){\/calendar_template}/s", $this->tagdata, $matches);
				$this->config['template'] = $matches[1];
			}

		}

		$this->EE->load->library('calendar', $this->config);
		return $this->EE->calendar->generate($year,$month,$data);

	}
}
/* End of file */ 
/* Location: ./system/expressionengine/third_party/ */ 