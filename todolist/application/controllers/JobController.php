<?php
class JobController extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('JobModel');
		$this->load->model('CommentModel');
	}
	public function index()
	{
		$this->read();
	}
	public function read()
	{
		$jobList = $this->JobModel->read();
		// $data['jobList'] = $jobList;
		// $object['controller'] = $this;
		$data = array(
			'jobList' => $jobList,
			'controller' => $this
		);
		$this->load->view('JobView', $data);
	}
	public function insert()
	{
		$jobName = $this->input->post('jobName');
		$jobType = $this->input->post('jobType');
		$nvId = $this ->session->userdata('nv_id');
		$data = 
		array(
        	'job_name' => $jobName,
        	'job_startdate' => '0000-00-00',
        	'job_enddate' => '0000-00-00',
        	'job_finishdate' => '0000-00-00',
        	'job_type' => $jobType,
        	'nv_id' => $nvId,
        	'job_status' => 0
    	);

    	$this->JobModel->insert($data);
	}
	public function delete()
	{
		$jobId = $this->input->post('jobId');
		$this->JobModel->delete($jobId);
	}
	public static function formatEnddate($endDate, $status)
	{
		$date = getdate();
		$day = $date['year']."-".$date['mon']."-".$date['mday'];
		$today = strtotime($day);
		$end = strtotime($endDate);
		if($endDate != '0000-00-00')
        {
        	if($status == 0)//neu chua lam xong
        	{
        	if($end >= $today)//ngay het han chua toi
        	{
            	echo 
            	'<div style="font-weight:bold;">
            	<img src="'
            	.base_url("assets/img/icons8-time-machine-15.png").
            	'" style="padding-right:3px;">'
            	.$endDate.
            	'</div>';
        	} else if($end < $today)//ngay het han truoc ngay hom nay
        	{
        		echo 
            	'<div style="font-weight:bold; color:red;">
            	<img src="'
            	.base_url("assets/img/icons8-time-machine-15.png").
            	'" style="padding-right:3px;">'
            	.$endDate.
            	'</div>';
        	}
        	} else //neu da lam xong
        	{
        		echo 
            	'<div style="font-weight:bold; color:blue;">
            	<img src="'
            	.base_url("assets/img/icons8-time-machine-15.png").
            	'" style="padding-right:3px;">'
            	.$endDate.
            	'</div>';
        	}
        } 
	}
	public function updateStatus()
	{
		$jobId = $this->input->post('jobId');
		$status = $this->input->post('status');

		$this->JobModel->updateStatus($jobId, $status);
	}
	public function loadModalEdit()
	{
		$jobId = $this->input->post('jobId');
		$output = array();
		$data = $this->JobModel->readInfoJob($jobId);
		foreach ($data as $row) 
		{
			$output['job_name'] = $row['job_name'];
			$output['job_enddate'] = $row['job_enddate'];
			$output['job_startdate'] = $row['job_startdate'];
			$output['job_content'] = $row['job_content'];
		}

		$output['commentArea'] = $this->readComment($jobId);
		$output['partnerArea'] = $this->readPartner($jobId);

		echo json_encode($output);
	}
	public function readComment($jobId)
	{
		$data = $this->CommentModel->read($jobId);
		$output = "";
		foreach ($data as $row) 
		{
			$output .=
			'<tr>
                <td>
                    <div>
                        <div style="font-weight: bold; font-size: 15px;float: left;">'.$row['nv_firstname'].' '.$row['nv_lastname'].'</div>
                        <div style="font-size: 10px; float: left;padding-top: 1px;padding-left: 10px;">'.$row['cm_date'].'</div>
                    </div>
                    <div style="clear: both;">
                    <input type="text" style="border:none;width:100%;" value="'.$row['cm_contents'].'" readonly class="cmt'.$row['cm_id'].'">
                    </div>
                </td>
                ';
              if($this->session->userdata('nv_email') == "admin@gmail.com")
              {
              		$output .= '<td class="td-actions text-right" style="padding-top: 10px;float:right;">
                    <button type="button" rel="tooltip" title="Edit Comment" class="btn btn-primary btn-link btn-sm editCommentBtn" anlong="'.$row['cm_id'].'">
                        <i class="material-icons">edit</i>
                    </button>
                    <button type="button" rel="tooltip" title="Delete Comment" class="btn btn-danger btn-link btn-sm deleteCommentBtn" anlong="'.$row['cm_id'].'">
                        <i class="material-icons">close</i>
                    </button>
                	</td>';
              } else {
              	if($this->session->userdata('nv_id') == $row['nv_id'])
              	{
              		$output .= '<td class="td-actions text-right" style="padding-top: 10px;float:right;">
                    <button type="button" rel="tooltip" title="Edit Comment" class="btn btn-primary btn-link btn-sm editCommentBtn" anlong="'.$row['cm_id'].'">
                        <i class="material-icons">edit</i>
                    </button>
                    <button type="button" rel="tooltip" title="Delete Comment" class="btn btn-danger btn-link btn-sm deleteCommentBtn" anlong="'.$row['cm_id'].'">
                        <i class="material-icons">close</i>
                    </button>
                	</td>';
              	}
              }
              $output .= '</tr>';
		}
		return $output;
	}
	public function update()
	{
		$jobId = $this->input->post('jobId');
		$string = $this->input->post('string');
		$stringVal = $this->input->post('stringVal');
		$this->JobModel->update($jobId, $string, $stringVal);
	}

	public function updateDate()
	{
		$jobId = $this->input->post('jobId');
		$string = $this->input->post('string');
		$startDate = $this->input->post('startDate');
		$endDate = $this->input->post('endDate');

		$start = strtotime($startDate);
		$end = strtotime($endDate);

		if($start > $end)//ngay bat dau lon hon ngay ket thuc
		{
			echo "Ngày bắt đầu phải trước ngày kết thúc";
		} else {
			echo "";
			if($string == "job_startdate")
			{
				$this->JobModel->update($jobId, $string, $startDate);
			} else {
				$this->JobModel->update($jobId, $string, $endDate);
			}
		}
	}

	public function insertComment()
	{
		$content = $this->input->post('content');
		$jobId = $this->input->post('jobId');
		$date = getdate();
		$day = $date['year']."-".$date['mon']."-".$date['mday'];
		$nvId = $this ->session->userdata('nv_id');
		$data = 
		array(
        	'cm_contents' => $content,
        	'job_id' => $jobId,
        	'nv_id' => $nvId,
        	'cm_date' => $day
    	);

    	$this->CommentModel->insert($data);

    	$output = $this->readComment($jobId);
    	echo $output;
	}
	public function deleteComment()
	{
		$cmId = $this->input->post('cmId');
		$jobId = $this->input->post('jobId');
		$this->CommentModel->delete($cmId);

		//reload comment table
		$output = $this->readComment($jobId);
    	echo $output;
	}
	public function updateCmt()
	{
		$cmId = $this->input->post('cmId');
		$content = $this->input->post('content');
		$jobId = $this->input->post('jobId');

		$this->CommentModel->update($cmId, $content);

		//reload comment table
		$output = $this->readComment($jobId);
    	echo $output;
	}
	public function updatePartner()
	{
		$jobId = $this->input->post('jobId');
		$nvId = $this->input->post('nvId');
		$string = "nv_partners";
		$data = $this->JobModel->readPartner($jobId);
		if(empty($data))
		{
			$data = array();
			array_push($data, (int)$nvId);
		} else {
		array_push($data, (int)$nvId);
		}
		$stringVal = json_encode($data);
		$this->JobModel->update($jobId, $string, $stringVal);

		//reload
		$output = $this->readNhanvien($jobId);
    	echo $output;
	}	
	public function removePartner()
	{
		$jobId = $this->input->post('jobId');
		$nvId = $this->input->post('nvId');
		$string = "nv_partners";
		$data = $this->JobModel->readPartner($jobId);
		foreach ($data as $key) 
		{
			if($key == $nvId)
			{
				$index = array_search($key, $data);
				unset($data[$index]);
				break;
			}
		}
		$op = "[";
		foreach ($data as $row) 
		{
			$op = $op.$row.",";
		}
		$stringVal = rtrim($op, ", ");
		$stringVal .= "]";
		$this->JobModel->update($jobId, $string, $stringVal);

		//reload
		$output = $this->readPartner($jobId);
    	echo $output;
	}
	public function loadreadPartner()
	{
		$jobId = $this->input->post('jobId');

		$output = $this->readPartner($jobId);
    	echo $output;
	}
	public function readPartner($jobId)
	{
		$data = $this->JobModel->readPartner($jobId);
		$output = "";
		if(empty($data))
		{
			$output = "";
		} else {
		foreach ($data as $key) 
		{
			$name = $this->JobModel->readNvName($key);
			$output .=
			'<tr>
                <td style="font-weight: bold;">'.$name.'</td>
                    <td class="td-actions text-right">
                    <button type="button" rel="tooltip" title="Remove" class="btn btn-danger btn-link btn-sm removePartnerBtn" anlong="'.$key.'">
                    <i class="material-icons">close</i>
                    </button>
                </td>
            </tr>';
		}
		}
		return $output;
	}
	public function loadModalNhanvien()
	{
		$jobId = $this->input->post('jobId');

		$output = $this->readNhanvien($jobId);
    	echo $output;
	}

	public function readNhanvien($jobId)
	{
		$dataPartner = $this->JobModel->readPartner($jobId);
		$dataNhanvien = $this->JobModel->readNhanvien();
		if(!empty($dataPartner))
		{
		//loai bo cac nhan vien da co trong job nay
		foreach ($dataPartner as $pn) 
		{
			foreach ($dataNhanvien as $nv) 
			{
				if($nv["nv_id"] == $pn)
				{
					$index = array_search($nv, $dataNhanvien);
					unset($dataNhanvien[$index]);
					break;
				}
			}
		}
		}
		$output = "";
		foreach ($dataNhanvien as $row) 
		{
			$output .= '<tr>    
                            <td>'.$row["nv_id"].'</td>                        
                            <td>'.$row["nv_firstname"].' '.$row["nv_lastname"].'</td>
                            <td class="td-actions text-right" style="float: right;">
                              <button style="float: right;" type="button" rel="tooltip" title="Add to Job" class="btn btn-danger btn-link btn-sm addNvModalBtn" anlong="'.$row["nv_id"].'">
                                <i class="material-icons">add</i>
                              </button>
                            </td>
                          </tr> ';
		}
		return $output;
	}

}
?>
