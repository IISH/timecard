<?php

//
class TCDateTime {
	private $date = '';

	public function __construct() {
		$this->date = new DateTime();
	}

	//
	public function get() {
		return $this->date;
	}

	//
	public function set($date) {
		$this->date = clone $date;
	}

	//
	public function setFromString($date_as_string, $format = 'Y-m-d') {
		if ( method_exists ( new DateTime(), "createFromFormat" ) ) {
			$this->date = DateTime::createFromFormat($format, $date_as_string);
		} else {
			$newDate = strtotime($date_as_string);
			$this->date->setDate( date('Y', $newDate), date('m', $newDate), date('d', $newDate));
			$this->date->setTime( date('H', $newDate), date('i', $newDate), date('s', $newDate));
		}
	}

	//
	public function getToString($format = 'Y-m-d') {
		return $this->get()->format($format);
	}

	//
	private function getNext( $what, $what_old_style ) {
		$date = clone $this->date;

		if ( method_exists ( new DateTime(), "add" ) ) {
			$date->add( new DateInterval($what) );
		} else {
			$newDate = strtotime($date->format("Y-m-d H:i:s") . ' ' . $what_old_style);
			$date->setDate( date('Y', $newDate), date('m', $newDate), date('d', $newDate));
		}

		return $date;
	}

	//
	public function getNextDay() {
		return $this->getNext("P1D", "+1 day");
	}

	//
	public function getNextMonth() {
		return $this->getNext("P1M", "+1 month");
	}

	//
	public function getNextYear() {
		return $this->getNext("P1Y", "+1 year");
	}

	//
	private function add( $what, $what_old_style ) {
		$date = $this->date;

		if ( method_exists ( new DateTime(), "add" ) ) {
			$date->add( new DateInterval($what) );
		} else {
			$newDate = strtotime($date->format("Y-m-d H:i:s") . ' ' . $what_old_style);
			$date->setDate( date('Y', $newDate), date('m', $newDate), date('d', $newDate));
		}

		return $date;
	}

	//
	public function addDay() {
		return $this->add("P1D", "+1 day");
	}

	//
	public function addMonth() {
		return $this->add("P1M", "+1 month");
	}

	//
	public function addYear() {
		return $this->add("P1Y", "+1 year");
	}

	//
	private function getPrev( $what, $what_old_style ) {
		$date = clone $this->date;

		if ( method_exists ( new DateTime(), "sub" ) ) {
			$date->sub( new DateInterval($what) );
		} else {
			$newDate = strtotime($date->format("Y-m-d H:i:s") . ' ' . $what_old_style);
			$date->setDate( date('Y', $newDate), date('m', $newDate), date('d', $newDate));
		}

		return $date;
	}

	//
	public function getPrevDay() {
		return $this->getPrev("P1D", "-1 day");
	}

	//
	public function getPrevMonth() {
		return $this->getPrev("P1M", "-1 month");
	}

	//
	public function getPrevYear() {
		return $this->getPrev("P1Y", "-1 year");
	}

	//
	private function sub( $what, $what_old_style ) {
		$date = $this->date;

		if ( method_exists ( new DateTime(), "sub" ) ) {
			$date->sub( new DateInterval($what) );
		} else {
			$newDate = strtotime($date->format("Y-m-d H:i:s") . ' ' . $what_old_style);
			$date->setDate( date('Y', $newDate), date('m', $newDate), date('d', $newDate));
		}

		return $date;
	}

	//
	public function subDay() {
		return $this->sub("P1D", "-1 day");
	}

	//
	public function subMonth() {
		return $this->sub("P1M", "-1 month");
	}

	//
	public function subYear() {
		return $this->sub("P1Y", "-1 year");
	}

	//
	public function getLastDay() {
		return date_format($this->date, "t");
	}

	//
	public function getLastDate() {
		$d = clone $this->date;
		$d->setDate(date_format($this->date, 'Y'), date_format($this->date, 'm'), date_format($this->date, 't'));
		return $d;
	}

	//
	public function getFirstDate() {
		$d = clone $this->date;
		$d->setDate(date_format($this->date, 'Y'), date_format($this->date, 'm'), 1);
		return $d;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\ndate time: " . $this->get()->format('Y-m-d H:i:s') . "\n";
	}
}
