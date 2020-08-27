<?php

namespace App\Services;

use Firebase\JWT\JWT;
use App\Entity\User;



class JwtAuth{
    
    public $manager;
    public $key;
    
    public function __contruct($manager){
        $this->manager = $manager;
        $this->key = 'hola_que_tal_estas_este_es_el_master_fullstack43123432';
    }
    
    public function signup($email,$password,$gettoken = null){
        //Comprobar si el usuario existe
        $user = $this->manager->getRepository(User::class)->findOneBy([
            'email' => $email,
            'password' => $password
        ]);
        
        var_dump($user);
        die();
        $user = $this->manager->getRepository(User::class)->findOneBy([
            'email' => $email,
            'password' =>$password,
        ]);
        
        $signup = false;
        
        if(is_object($signup)){
            $signup = true;
        }
        
        //Si existe generar el token de jwt
        if($signup){
            $token = [
                'sub' => $user->getId(),
                'name' => $user->getName(),
                'surname' => $user->getSurname(),
                'email' => $user->getEmail(),
                'iat' => time(), //Cuando se ha creado el token
                'exp' => time() + (7*24*60*60) //El token caduca en una semana
                
            ];
            //Comprobar el flag gettoken, condicion
            
            $jwt = JWT::encode($token,$this->key,'HS256'); //genera el token (token,clave,algoritmo de cifrado)
            
            if(!empty($gettoken)){ //si nos llega el token
                $data = $jwt;
            }else{
                $decode = JWT::decode($jwt,$this->key,['HS256']); 
                $data = $decode;
            }

        }else{
            $data = [
                'status' => 'error',
                'message' => 'Login incorrecto.'
            ];
        }
        
        
        
        //Devolver los datos
        
        return "Hola mundo desde el servicio";
    }
}