<?php

/**
 * File Name : TeamwiseHolidays.php
 * Author : Amit Sabal
 * Created On : 13 Aug 2013
 * Organisation : Bhea Knowledge Technology (P) Ltd.
 * Motive : To check the Holidays of the different teams and assign the case closing date
 */


if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

	class TeamHoliday {

		/**
		 * holiday list
		 */
		function holiday_list($team_id,$team_set_id,$increamentd_date) {

			global $db;
			$h_date1 =array();
			$s_time ='08:30:00';

			$qry3 =" SELECT t1.id, t1.name, t3.name list_name, t3.holiday_date
						FROM bhea_holidays t1

						INNER JOIN  bhea_holidays_bhea_holidays_list_c t2
						ON ( t1.id = t2.bhea_holidays_bhea_holidays_listbhea_holidays_ida )

						INNER JOIN  bhea_holidays_list t3
						ON ( t3.id = t2.bhea_holidays_bhea_holidays_listbhea_holidays_list_idb )

						WHERE t1.team_id = '$team_id '

						AND t1.deleted ='0'
						AND t2.deleted ='0'
						AND t3.deleted ='0' ";

			$rec3 = $db->query($qry3);

			while($res3=$db->fetchByAssoc($rec3)) {

				$h_id1		= $res3['id'];
				$h_date1[] 	= $res3['holiday_date'];
			}

			$date1 = substr($increamentd_date, 0, strpos( $increamentd_date, ' '));

			$timestamp = strtotime($date1);
			$days_2 = date('l', $timestamp);


			if( $days_2 =='Saturday' ) {

				$increamentd_date = date('Y-m-d'.' '.$s_time, strtotime($increamentd_date .' + 2 day'));

				$this->holiday_list($team_id,$team_set_id,$increamentd_date);

				$increamentd_date1 =$increamentd_date ;
				//$increamentd_date1 = date('Y-m-d'.' '.$s_time, strtotime($val1 .' + 1 day'));
			}
			else if( in_array ($date1, $h_date1) ) {

				$increamentd_date = date('Y-m-d'.' '.$s_time, strtotime($increamentd_date .' + 1 day'));

				$this->holiday_list($team_id,$team_set_id,$increamentd_date);

				//$increamentd_date1 =$increamentd_date ;	// Commented on 28Aug 2013
				$val1 =$increamentd_date ;					// Added on 28Aug 2013
				$increamentd_date1 = date('Y-m-d'.' '.$s_time, strtotime($val1 .' + 1 day'));
			}
			/*else if( ($days_2 =='Sunday') && (in_array ($date1, $h_date1) ) ) {

				$increamentd_date = date('Y-m-d'.' '.$s_time, strtotime($increamentd_date .' + 1 day'));

				$this->holiday_list($team_id,$team_set_id,$increamentd_date);

				$increamentd_date1 =$increamentd_date ;
				$increamentd_date1 = date('Y-m-d'.' '.$s_time, strtotime($val1 .' + 1 day'));
			}	*/
			else{

			    $increamentd_date = $date1.' '.$s_time;
				$increamentd_date1=$increamentd_date;
			}
			return $increamentd_date1;
		}
		/**
	 	 * end
		 */

		function Holiday_Fn(&$bean, $event, $arguments) {

			global $db;
			$j=0;
			$id=$bean->id;
			$team_id = $bean->team_id;
			$team_set_id = $bean->team_set_id;
			$functional_tat = $bean->functional_tat_c;

			$start_hr = '08';
			$start_min = '30';
			$start_time ='08:30:00';

			$end_hr = '17';
			$end_min = '30';
			$end_time ='17:30:00';
			if($functional_tat != 'NA' || $functional_tat != '') {

				$hours = substr($functional_tat, 0, strpos( $functional_tat, '_'));
				//$hours = 24 ;
				$hours1 = $hours .':00:00' ;

			}
			else {
				$hours = 0;

			}

			$qry1 =" SELECT date_entered FROM cases WHERE id ='$id' and DELETED = 0 ";
			$rec1 = $db->query($qry1);
			$res1=$db->fetchByAssoc($rec1);

			$date_entered =$res1[date_entered];

			$entered_hrs = date('H:i:s', strtotime($date_entered));

			$h1=date('H',  strtotime($entered_hrs));
			$m1=date('i',  strtotime($entered_hrs));
			$s1=date('s',  strtotime($entered_hrs));

			$hr_sum = ($h1*60*60)+($m1*60)+($s1);
			$end_hr_sum = ($end_hr*60*60);

			$diff = $end_hr_sum - $hr_sum;

			if( $diff >= 0) {

				$worked_hr = gmdate("H:i:s", $diff);  			// Worked hours on the case in a particular day

				$increamentd_date = date('Y-m-d H:i:s', strtotime($date_entered .' + 1 day'));
				$val =	$this->holiday_list($team_id,$team_set_id,$increamentd_date);

				$increamentd_date = $val;
			}
			else{

				//$diff = -($diff);
				//$increamentd_date = date('Y-m-d H:i:s', strtotime($date_entered .'+ '.$diff.' seconds'));
				$increamentd_date = date('Y-m-d H:i:s', strtotime($date_entered .'+ 1 day'));
				$inc = $this->holiday_list($team_id,$team_set_id,$increamentd_date);
				$date2 = substr($inc, 0, strpos( $inc, ' '));
				$new_date= $date2.' '.$start_time;
				$increamentd_date = date('Y-m-d H:i:s', strtotime($new_date .'+ '.($hours*60*60).' seconds'));
			}

			$t3 = strtotime($worked_hr);
			$t4 = strtotime($hours1);
			$differ = $t4 - $t3;

			if($differ >= 0){

				$remaining_hrs = gmdate("H:i:s", $differ);			// remaining hours after the completion of the day

				$h=date('H',  strtotime($remaining_hrs));
				$m=date('i',  strtotime($remaining_hrs));
				$s=date('s',  strtotime($remaining_hrs));

				$sum = ($h*60*60)+($m*60)+($s);

				while( $sum >= (9*60*60)){

					$increamentd_date = date('Y-m-d'.' '.$start_time, strtotime($date_entered .' + 1 day'));
					$val2 = $this->holiday_list($team_id,$team_set_id,$increamentd_date);
					$increamentd_date = $val2;		// Added on 28Aug 2013
					$sum = $sum - (9*60*60);
				}
				$increamentd_date1 = date('Y-m-d H:i:s', strtotime($increamentd_date  .'+ '.$sum.' seconds'));
			}
			else{

				$increamentd_date1 = date('Y-m-d H:i:s', strtotime($date_entered  .'+ '.$hours.' hours'));
			}

			$update_date = "UPDATE cases_cstm SET expected_resolution_date_c  = '$increamentd_date1' WHERE id_c = '$id' ";
			$db->query($update_date);				//exit;
		}
	}
?>
