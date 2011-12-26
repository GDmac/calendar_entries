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
	public $weekdays = array(
		
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


	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	public function week()
	{
		// first day of the week, parameter start_day (sunday, monday, etc)
		$start_day = date('N', strtotime($this->EE->TMPL->fetch_param('start_day','sunday') ) );
		if($start_day == false) $start_day = 7;

		// current date in parameter start
		$start = strtotime($this->EE->TMPL->fetch_param('start', false));
		if($start===false) $start = $this->EE->localize->set_localized_time($this->EE->localize->now);

		// set start to first day of the week
		$weekday = date('N',$start);

		if( $weekday != $start_day )
		{
			if($weekday < $start_day) $weekday = $weekday + 7;
			$start = $start - ($weekday - $start_day) * 86400;
		}

		// replace tags for start_on and stop_before parameters
		$calendar_entries_start = date('Y-m-d 00:00', $start);
		$calendar_entries_end = date('Y-m-d 00:00', $start + (7 * 86400));
		
		$this->tagdata = str_replace('{calendar_entries_start}', $calendar_entries_start, $this->tagdata);
		$this->tagdata = str_replace('{calendar_entries_end}', $calendar_entries_end, $this->tagdata);
		
		// TODO: move to session var, instead of altering the tag :-)
		$this->tagdata = str_replace('{exp:calendar_entries:week_items', "{exp:calendar_entries:week_items start=\"$start\"", $this->tagdata);

		return $this->tagdata;
	}


	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	public function week_items()
	{
		// from session or tag
		$start = $this->EE->TMPL->fetch_param('start');
		$today = date('Y-m-d',$this->EE->localize->set_localized_time($this->EE->localize->now));
		
		$data = array();
		
		foreach( $this->EE->TMPL->var_pair as $key => $params)
		{
			if (strncmp($key, 'item', 4) == 0)
			{
				$date    = (int) $params['entry_date'];
				$tmp     = preg_match("/{".preg_quote($key)."}(.*?){\/item}/s", $this->tagdata, $matches);
				$content = $matches[1];

				$data[date('Y-m-d',$date)][] = array(
					'date'=>$date, 
					'content'=>$content
				);
			}

			if (strncmp($key, 'calendar_template', 17) == 0)
			{
				$tmp = preg_match("/{".preg_quote($key)."}(.*?){\/calendar_template}/s", $this->tagdata, $matches);
				$this->config['template'] = $matches[1];
			}

		}

		$ret = '';

		// output week table
		$ret .= '<table class="calendar week">'.PHP_EOL;
		$ret .= '<tr>'.PHP_EOL;

		// header
		for ($i = 0; $i <= 6; $i++)
		{
			$ret .=  "<th>".date('D d M',($start + ($i  * 86400)))."</th>";
		}

		$ret .= '</tr>';
		$ret .= '<tr>';

		// data
		for ($i = 0; $i <= 6; $i++)
		{
			$day = date('Y-m-d', ($start + ($i  * 86400)));

			$ret .= '<td><div'.($day==$today?' class="today"':'').'>';

			if(isset($data[$day]))
			{
				foreach($data[$day] as $item) $ret .= $item['content'];
			}

			$ret .=  PHP_EOL.'</div></td>'.PHP_EOL;
		}
		$ret .= '</tr></table>';

		$ret .= '<p>';
		$ret .= '<a href="./'.date('Y-m-d',$start-(7*86400)).'">« prev</a>';
		$ret .= '&nbsp;';
		$ret .= '<a href="./'.date('Y-m-d',$start+(7*86400)).'">next »</a>';
		$ret .= '</p>';

		return $ret;

	}


}
/* End of file */ 
/* Location: ./system/expressionengine/third_party/ */ 