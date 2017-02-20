<?php

/**
 * Třída AttendeeCtrl zpracovává požadavky uživatele na zobrazení informací konkrétmího účastníka.
 */
class AttendeeCtrl extends AbstractCtrl {
    
    /**
     * Metoda process() zpracuje požadavek uživatele na zobrazení účastníků.
     * Např.: 
     *      /attendee/{attendee_id}     zobrazí konkrétního účastníka
     */
    public function process($params){
        // získáná číselníků typ_platby, typ_ucast a typ_ubyt
        $attendeeHelper = new AttendeeHelper();
        $eventHelper = new EventHelper();
        $this->data['attendee_types'] = $attendeeHelper->getAttendeeTypes();
        $this->data['payment_types'] = $attendeeHelper->getPaymentTypes();
        $event_id = $_COOKIE['event'];
        $this->data['accomodation_types'] = $attendeeHelper->getAccommodationTypes($event_id);
        $_SESSION['current-page']="attendee";
        if(!empty($params[0])){
            if($_POST){
                $birthday = $_POST['birthday'];
                if(!empty($birthday)){
                    $birthday = date("Y-m-d",strtotime($birthday));
                }
                $staff = 'Ne';
                if(isset($_POST['staff'])){
                    $staff = 'Ano';
                }
                $staff_type = 0;
                if(isset($_POST['staff_type'])){
                    $staff_type = 1;
                }
                $stored = 0;
                if($params[0]='new'){
                    $stored = $attendeeHelper->createAttendee($_POST['name'],$_POST['surname'],$_POST['street'],
                        $_POST['city'],$_POST['zip'],$_POST['country'],$_POST['idcard'],$_POST['gender'],$_POST['phone'],
                        $_POST['email'],$birthday,$_POST['movement'],$staff,$staff_type);
                } else {
                    $stored = $attendeeHelper->updateAttendee($params[0],$_POST['name'],$_POST['surname'],$_POST['street'],
                        $_POST['city'],$_POST['zip'],$_POST['country'],$_POST['idcard'],$_POST['gender'],$_POST['phone'],
                        $_POST['email'],$birthday,$_POST['movement'],$staff,$staff_type);
                }
                if($stored){
                    $this->addMessage("Změny byly úspěšně uloženy.");
                }
            }
            // zobrazení konkrétního účastníka
            $attendee = $attendeeHelper->getAttendee($params[0],$event_id);
            $this->data['attendee']=$attendee;
            $orders_by_days = $attendeeHelper->getOrdersByDays($params[0],$event_id);
            $this->data['orders_by_days']=$orders_by_days;
            $this->data['event_days']=$eventHelper->getEventDays($event_id);
            $this->view = 'attendee';
        } else {
            $this->redirectTo("error");
        }
    }
}

?>