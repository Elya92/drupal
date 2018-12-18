<?php
 
namespace Drupal\ex_form\Form;
 
use Drupal\Core\Form\FormBase; // Базовый класс Form API
use Drupal\Core\Form\FormStateInterface; // Класс отвечает за обработку данных
//use GuzzleHttp\Client;
 
/**
 * Наследуемся от базового класса Form API
 * @see \Drupal\Core\Form\FormBase
 */
class ExForm extends FormBase {
 
 // метод, который отвечает за саму форму - кнопки, поля
 public function buildForm(array $form, FormStateInterface $form_state) {
 
  $form['firstname'] = [
   '#type' => 'textfield',
   '#title' => $this->t('Ваше имя'),
   '#required' => TRUE,
  ];
  
  $form['lastname'] = [
   '#type' => 'textfield',
   '#title' => $this->t('Ваша фамилия'),
   '#required' => TRUE,
  ];
  
  $form['subject'] = [
   '#type' => 'textfield',
   '#title' => $this->t('Тема письма'),
   '#required' => TRUE,
  ];
 
  $form['message'] = [
   '#type' => 'textarea',
   '#title' => $this->t('Письмо'),
   '#required' => TRUE,
  ];
  
  $form['email'] = [
   '#type' => 'textfield',
   '#title' => $this->t('E-mail'),
   '#required' => TRUE,
  ];
  // Add a submit button that handles the submission of the form.
  $form['actions']['submit'] = [
   '#type' => 'submit',
   '#value' => $this->t('Отправить'),
  ];
 
  return $form;
 }
 
 // метод, который будет возвращать название формы
 public function getFormId() {
  return 'ex_form_exform_form';
 }
 
 // ф-я валидации
 public function validateForm(array &$form, FormStateInterface $form_state) {
  $email = $form_state->getValue('email');
  $is_email = preg_match('/[\w-_]+@[a-z]+\.[a-z]{2,3}/', $email);
 
  if (!$is_email) {
   $form_state->setErrorByName('email', $this->t('Некорректный e-mail!'));
  }
 }
 
 // действия по сабмиту
 public function submitForm(array &$form, FormStateInterface $form_state) {
  $firstname = $form_state->getValue('firstname');
  $lastname = $form_state->getValue('lastname');
  $email = $form_state->getValue('email');
  $subject = $form_state->getValue('subject');
  $message = $form_state->getValue('message');
  
  $client = \Drupal::httpClient();
  
  ini_set("SMTR", "mail.gmail.com");
  ini_set("smtp_port", "25");
  if (mail($email, $subject, $message, 'From: auto@myproject.com')){
   drupal_set_message(t('Письмо успеешно отправлено.'));
   $this->recordLog($email, $subject, $message);
   $response = $client->request('POST', 'https://api.hubapi.com/contacts/v1/contact/?hapikey=f529abcf-8d7f-4393-8b56-fe6c468d7cda', [
    'json' => [
	 "properties" => [
	 [
	  "property" => "email",
      "value" => $email
     ],
     [
      "property" => "firstname",
      "value" => $firstname
     ],
     [
      "property" => "lastname",
      "value" => $lastname
     ],
     [
      "property" => "website",
      "value" => "http://informatics.by"
     ],
     [
      "property" => "company",
      "value" => "HelenMiatlitskaya"
     ],
     [
      "property" => "phone",
      "value" => "555-122-2323"
     ],
     [
      "property" => "address",
      "value" => "25 First Street"
     ],
     [
      "property" => "city",
      "value" => "Cambridge"
     ],
     [
      "property" => "state",
      "value" => "MA"
     ],
     [
      "property" => "zip",
      "value" => "02139"
     ]
	 ]
	]
   ]);
   var_dump($response);
  }
	 //'email' => $email,
	 //'firstname' => $firstname,
	 //'lastname' => $lastname,
	 //'website' => 'http://hubspot.com',
	 //'company' => 'HubSpot',
	 //'phone' => '555-122-2323',
	 //'address' => '25 First Street',
	 //'city' => 'Cambridge',
	 //'state' => 'MA',
	 //'zip' => '02139'
  else{
   drupal_set_message(t('Письмо не отправлено.'));
  }
 }
 
 public function recordLog($email, $subject, $message) {
  $file = 'modules/custom/ex_form/message.log';
  $separator = "=============================\r\n";
  $fOpen = fopen($file, 'a');
  fwrite($fOpen, $separator);
  fwrite($fOpen, 'Email: '.$email."\r\n");
  fwrite($fOpen, 'Subject: '.$subject."\r\n");
  fwrite($fOpen, 'Message:'.$message."\r\n");
  fclose($fOpen);
 }
 
}