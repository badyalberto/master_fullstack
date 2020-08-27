<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;

use App\Entity\User;
use App\Entity\Video;
use App\Services\JwtAuth;

class UserController extends AbstractController
{
    
    private function resjson($data){
        //Serializar datos con servicio serializer
        $json = $this->get('serializer')->serialize($data,'json');
        
        //Response con httpfoundation
        $response = new Response();
       
        //Asignar contenido a la respuesta
        $response->setContent($json);
        
        //Indicar formato de respuesta
        $response->headers->set('Content-Type','application/json');
        
        //Devolver respuesta
        return $response;
        
    }

    public function index()
    {
        
        $user_repo = $this->getDoctrine()->getRepository(User::class);
        $video_repo = $this->getDoctrine()->getRepository(Video::class);
        
        $users = $user_repo->findAll();
        
        $user = $user_repo->find(1);
   
        $videos = $video_repo->findAll();
        
        $data = [
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ];
        
        /*foreach($users as $user){
            echo "<h1>{$user->getName()} {$user->getSurname()}</h1>";
            
            foreach($user->getVideos() as $video){
                if(!empty($video)){
                    echo "<p>{$video->getTitle()} - {$video->getUser()->getEmail()}</p>";
                }
                
            }
        }
        
        die();*/
        return $this->resjson($videos);
    }
    
    public function create(Request $request){
        //Recoger los datos por POST
        $json = $request->get('json',null); //Recoge el objeto llamado json
        
        //Decodificar el json
        $params = json_decode($json);
        
        //Respuesta por defecto
        $data = [
            'status' => 'error',
            'code' => 200,
            'message' => 'El usuario no se ha credo.'
        ];
        
        //Comprobar y validar datos
        if($json != null){
            
            $name = (!empty($params->name)) ? $params->name : null; //Si no esta vacio accedemos al parametro name
            $surname = (!empty($params->surname)) ? $params->surname : null;
            $email = (!empty($params->email)) ? $params->email : null;
            $password = (!empty($params->password)) ? $params->password : null;
            
            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [ 
                new Email() //indica la restricciones
            ]);
            
            if(!empty($email) && count($validate_email) == 0 && !empty($password) && !empty($name) && !empty($surname)){
                //Si la validacion es correcta, crear el objeto del usuario
                
                $user = new User();
                
                $user->setName($name);
                $user->setSurname($surname);
                $user->setEmail($email);
                $user->setRole('ROLE_USER');
                $user->setCreatedAt(new \DateTime('now'));
 
                //Cifrar la contraseña
                $pwd = hash('sha256',$password);
                $user->setPassword($pwd);
                
                //Comprobar si el usuario existe (duplicados)
                $doctrine = $this->getDoctrine();
                $em = $doctrine->getManager();
                
                $user_repo = $doctrine->getRepository(User::class);
                $isset_user = $user_repo->findBy(array(
                    'email' => $email //Where email = $email
                ));
                
                if(count($isset_user) == 0){
                    //guardo el usuario
                    $em->persist($user);
                    $em->flush();
                    
                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Usuario creado correctamente',
                        'user' => $user
                    ];
                    
                }else{
                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'El usuario ya existe.'
                    ];
                }
                
        
                //Si no existe, guardarlo en la bd
            }
        }   
        
        //Hacer respuesta en json
        return $this->resjson($data);
        
    }
    
    public function login(Request $request,JwtAuth $jwtauth){
        //Recibir los datos por post
        $json = $request->get('json',null); //si no se recibe sera null
        $params = json_decode($json); //Convierte en un variable de php
        
        //Array por defecto para devolver
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'El usuario no se ha podido identificar'
        ];
        
        //Comprobar y validar datos
        if($json != null){
            $email = (!empty($params->email)) ? $params->email : null;
            $password = (!empty($params->password)) ? $params->password : null;
            $gettoken = (!empty($params->gettoken)) ? $params->gettoken : null;
            
            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]);
            
            if(!empty($email) && !empty($password) && count($validate_email) == 0){
                //Cifrar la contraseña
                $pwd = hash('sha256',$password);
                
                //Si todo es valido, llamaremos a un servicio para identificar al usuario(jwt,token o un objeto)
                if($gettoken){
                   $signup = $jwtauth->signup($email, $pwd,$gettoken);
                }else{
                    $signup = $jwtauth->signup($email, $pwd);
                }
                
                return new JsonRespons($signup);
            }
        }
        
        //Si nos devuelve bien los datos, respuesta
        return $this->resjson($data);
    }
}
