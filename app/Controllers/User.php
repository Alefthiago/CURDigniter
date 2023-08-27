<?php

namespace App\Controllers;

use App\Models\User as ModelUser;
//      Este CONTROLLER trata das VIEWS LOGIN E RISTRATION  //

class User extends BaseController
{

    public function index()
    {
        //      Verificação de login do usuario      //
        if (!session()->has('user')) {
            return view('login');
        } else {
            return redirect()->route('homePage');
        }
    }

    public function login()
    {
        //      Obtendo os dados enviados do request     //
        //      Os inputs são email e pass      //
        $data = (object) array(
            'US_EMAIL' => $this->request->getPost()['email'],
            'US_PASS' => $this->request->getPost()['pass']
        );
        try {
            $modalUser = new ModelUser();
            $userFound = $modalUser->select('US_EMAIL, US_PASS, US_ID')
                ->where('US_EMAIL', $data->US_EMAIL)
                ->first();
            if (!$userFound or !password_verify($data->US_PASS, $userFound->US_PASS)) {
                return redirect()->route('loginPage')
                    ->with('error', 'Dados inválidos!')
                    ->withInput();
            }
            //      Criando uma session com os dados do usuário     //
            session()->set('user', (object) array(
                'email' => $userFound->US_EMAIL,
                'id' => $userFound->US_ID
            ));
            return redirect()->route('homePage');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            return redirect()->route('loginPage')
                ->with('error', ERRORMESSAGE)
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->route('loginPage')
                ->with('error', ERRORMESSAGE)
                ->withInput();
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->route('loginPage');
    }

    public function registration()
    {
        if (!session()->has('user')) {
            return view('registration');
        } else {
            return redirect()->route('homePage');
        }
    }

    public function created()
    {
        //      Inputs email, pass e confirmPass; São retornados como um array      //
        if ($this->request->getPost()['pass'] != $this->request->getPost()['confirmPass']) {
            return redirect()->route('registrationPage')
                ->with('error', 'As senhas devem ser iguais!')
                ->withInput();
        };

        $data = (object) array(
            'US_EMAIL' => $this->request->getPost()['email'],
            'US_PASS' => password_hash($this->request->getPost()['pass'], PASSWORD_ARGON2I)
        );
        //      ERRORMESSAGE é uma constante criada com uma mensagem padrão para erros       //
        try {
            $modelUser = new ModelUser();
            $inserted = $modelUser->insert($data);
            if ($inserted) {
                session()->set('user', [$data->US_EMAIL, $data->US_ID]);
                return redirect()->route('homePage');
            } else {
                return redirect()->route('registrationPage')
                    ->with('error', ERRORMESSAGE)
                    ->withInput();
            }
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            if (preg_match('/duplicate/i', $e->getMessage(), $matches)) {
                return redirect()->route('registrationPage')
                    ->with('error', 'E-mail inválido!')
                    ->withInput();
            } else {
                return redirect()->route('registrationPage')
                    ->with('error', ERRORMESSAGE)
                    ->withInput();
            }
        } catch (\Exception $e) {
            return redirect()->route('registrationPage')
                ->with('error', ERRORMESSAGE)
                ->withInput();
        }
    }
}
