<?php require_once ('header.php');

$idUser = $_POST['id'];

$buscaPerfil = $pdo->prepare('select * from `ct_permission` ');
$buscaPerfil->execute();
$registros = $buscaPerfil->fetchAll(PDO::FETCH_CLASS);

$usuariosCadastrados = $pdo->prepare('select * from `ct_usuario` where `id` = :id ');
$usuariosCadastrados->execute( array( ":id" => $idUser ) );
$listaUsusarios = $usuariosCadastrados->fetch(PDO::FETCH_ASSOC);

$dataUser = $pdo->prepare("select * from `ct_datauser` where iduser = :id");
$dataUser->execute(array(":id" => $_POST['id']));
$registerDataUser = $dataUser->fetchAll(PDO::FETCH_CLASS);


if( isset( $_POST['atualizar'] ) )
{
  $nome   = $_POST['nome'];
  $login  = $_POST['login'];
  $senha  = md5($_POST['senha']);
  $idUser = $_POST['id'];

  $reserva    = $_POST['reserva'];
  $mapa       = $_POST['mapa'];
  $financeiro = $_POST['financeiro'];
  $caixa      = $_POST['caixa'];
  $comissao   = $_POST['pcomissao'];
  $folha      = $_POST['folha'];
  $administrador   = $_POST['administrador'];
  $gerente         = $_POST['gerente'];
  $revendedor      = $_POST['revendedor'];
  $pousada         = $_POST['pousada'];
  $naopermitirpagamentoreserva = $_POST['naopermitirpagamentoreserva'];
  $financeirotwo = $_POST['financeirotwo'];

  if( isset($administrador) )
  {
      $perfil = 2;
      if( !empty( $_POST['senha'] ) )
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao, `password` = :senha where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":senha" => $senha, ":id" => $idUser ) );
      }else
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":id" => $idUser ) );
      }
  }
  elseif( isset($gerente) )
    {
        $perfil = 3;
        if( !empty( $_POST['senha'] ) )
        {
            $updateUsuario = $pdo->prepare(
                "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao, `password` = :senha where `id` = :id ");
            $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":senha" => $senha, ":id" => $idUser ) );
        }else
        {
            $updateUsuario = $pdo->prepare(
                "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao where `id` = :id ");
            $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":id" => $idUser ) );
        }
    }
  elseif ( isset( $reserva ) )
  {
      if(isset($comissao) and empty($folha))
      {
          $perfil = 14;
          if( !empty( $_POST['senha'] ) )
          {
              $updateUsuario = $pdo->prepare(
                  "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao, `password` = :senha where `id` = :id ");
              $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":senha" => $senha, ":id" => $idUser ) );
          }else
          {
              $updateUsuario = $pdo->prepare(
                  "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao where `id` = :id ");
              $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":id" => $idUser ) );
          }
      }
      elseif (isset($folha) and empty($comissao))
      {
          $perfil = 13;
          if( !empty( $_POST['senha'] ) )
          {
              $updateUsuario = $pdo->prepare(
                  "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao, `password` = :senha where `id` = :id ");
              $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":senha" => $senha, ":id" => $idUser ) );
          }else
          {
              $updateUsuario = $pdo->prepare(
                  "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao where `id` = :id ");
              $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":id" => $idUser ) );
          }
      }
      elseif (isset($financeirotwo) and empty($comissao) and  empty($folha))
      {
          $perfil = 12;
          if( !empty( $_POST['senha'] ) )
          {
              $updateUsuario = $pdo->prepare(
                  "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao, `password` = :senha where `id` = :id ");
              $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":senha" => $senha, ":id" => $idUser ) );
          }else
          {
              $updateUsuario = $pdo->prepare(
                  "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao where `id` = :id ");
              $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":id" => $idUser ) );
          }
      }
      elseif (isset($financeirotwo) and isset($comissao) and isset($folha))
      {
          $perfil = 15;
          if( !empty( $_POST['senha'] ) )
          {
              $updateUsuario = $pdo->prepare(
                  "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao, `password` = :senha where `id` = :id ");
              $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":senha" => $senha, ":id" => $idUser ) );
          }else
          {
              $updateUsuario = $pdo->prepare(
                  "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao where `id` = :id ");
              $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":id" => $idUser ) );
          }
      }
      elseif (isset($comissao) and isset($folha))
      {
          $perfil = 4;
          if( !empty( $_POST['senha'] ) )
          {
              $updateUsuario = $pdo->prepare(
                  "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao, `password` = :senha where `id` = :id ");
              $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":senha" => $senha, ":id" => $idUser ) );
          }else
          {
              $updateUsuario = $pdo->prepare(
                  "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao where `id` = :id ");
              $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":id" => $idUser ) );
          }
      }
      else{
          $perfil = 1;
          if( !empty( $_POST['senha'] ) )
          {
              $updateUsuario = $pdo->prepare(
                  "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao, `password` = :senha where `id` = :id ");
              $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":senha" => $senha, ":id" => $idUser ) );
          }else
          {
              $updateUsuario = $pdo->prepare(
                  "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao where `id` = :id ");
              $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":id" => $idUser ) );
          }
      }

  }

  elseif (isset($revendedor) )
  {
      $perfil = 9;
      if( !empty( $_POST['senha'] ) )
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao, `password` = :senha where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":senha" => $senha, ":id" => $idUser ) );
      }else
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":id" => $idUser ) );
      }
  }
  elseif (isset($pousada) )
  {
      $perfil = 8;
      if( !empty( $_POST['senha'] ) )
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao, `password` = :senha where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":senha" => $senha, ":id" => $idUser ) );
      }else
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":id" => $idUser ) );
      }
  }
  elseif (isset($naopermitirpagamentoreserva) )
  {
      $perfil = 11;
      if( !empty( $_POST['senha'] ) )
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao, `password` = :senha where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":senha" => $senha, ":id" => $idUser ) );
      }else
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":id" => $idUser ) );
      }
  }
  elseif (isset($financeirotwo) )
  {
      $perfil = 12;
      if( !empty( $_POST['senha'] ) )
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao, `password` = :senha where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":senha" => $senha, ":id" => $idUser ) );
      }else
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":id" => $idUser ) );
      }
  }
  elseif (isset($comissao) and isset($folha) )
  {
      $perfil = 4;
      if( !empty( $_POST['senha'] ) )
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao, `password` = :senha where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":senha" => $senha, ":id" => $idUser ) );
      }else
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":id" => $idUser ) );
      }
  }
  elseif (isset($mapa) and empty($reserva))
  {

      $perfil = 5;
      if( !empty( $_POST['senha'] ) )
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao, `password` = :senha where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":senha" => $senha, ":id" => $idUser ) );
      }else
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":id" => $idUser ) );
      }
  }
  elseif (isset($financeiro) and empty($reserva))
  {

      $perfil = 7;
      if( !empty( $_POST['senha'] ) )
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao, `password` = :senha where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":senha" => $senha, ":id" => $idUser ) );
      }else
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":id" => $idUser ) );
      }
  }
  elseif (isset($caixa))
  {

      $perfil = 10;
      if( !empty( $_POST['senha'] ) )
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao, `password` = :senha where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":senha" => $senha, ":id" => $idUser ) );
      }else
      {
          $updateUsuario = $pdo->prepare(
              "update `ct_usuario` set `firstname` = :nome, `username` = :login, `idpermission` = :permissao where `id` = :id ");
          $updateUsuario->execute( array( ":nome" => $nome, ":login" => $login, ":permissao" => $perfil, ":id" => $idUser ) );
      }
  }
  $buscaPerfil = $pdo->prepare('select * from `ct_permission` ');
  $buscaPerfil->execute();
  $registros = $buscaPerfil->fetchAll(PDO::FETCH_CLASS);

  $usuariosCadastrados = $pdo->prepare('select * from `ct_usuario` where `id` = :id ');
  $usuariosCadastrados->execute( array( ":id" => $idUser ) );
  $listaUsusarios = $usuariosCadastrados->fetch(PDO::FETCH_ASSOC);

  echo( "<div class='alert alert-success' role='alert'>Usuário ".$nome." atualizado </div>" );

}

?>
<style>
    .col-lg-6,.col-lg-4{margin-bottom: 20px;}
    label{font-weight: bold; color: black;}
    ::placeholder{color: black; font-weight: bold;}
</style>
<!-- PAGE CONTENT-->
<div class="page-content--bgf7">
    <!-- BREADCRUMB-->
    <section class="au-breadcrumb2">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="au-breadcrumb-content">
                        <div class="au-breadcrumb-left">
                            <span class="au-breadcrumb-span">Navegação:</span>
                            <ul class="list-unstyled list-inline au-breadcrumb__list">
                                <li class="list-inline-item active">
                                    <a href="./">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">Usuário: Editar Usuário</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END BREADCRUMB-->

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Informações do Usuário: "<?php echo( utf8_encode( $listaUsusarios['firstname']." ".$listaUsusarios['firtname'] )); ?></h4>

                </div>
                <div class="card-body">
                    <form action="" method="post">
                        <div class="col-lg-4 pull-left">
                            <label for="nome">Nome </label>
                            <input type="text" name="nome" id="nome" value="<?php echo( $listaUsusarios['firstname'] ); ?>"  class="form-control">
                        </div>
                        <div class="col-lg-4 pull-left">
                            <label for="senha">Login</label>
                            <input type="text" name="login" id="login" value="<?php echo( $listaUsusarios['username'] ); ?>"  class="form-control">

                        </div>
                        <div class="col-lg-4 pull-right">
                            <label for="senha">Senha de Acesso </label>
                            <input type="password" name="senha" id="senha" autocomplete="off"  class="form-control">

                        </div>
                        <h4>Permissões</h4>
                        <hr>
                        <div class="col-lg-6 pull-left">
                            <div class="input-group mb-3">
                                <input disabled type="text" class="form-control" placeholder="Reserva">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <?php if( $listaUsusarios['idpermission'] == 2 or $listaUsusarios['idpermission'] == 1 or
                                            $listaUsusarios['idpermission'] == 4 or $listaUsusarios['idpermission'] == 3 or $listaUsusarios['idpermission'] == 6
                                            or $listaUsusarios['idpermission'] == 11 or $listaUsusarios['idpermission'] == 12 or $listaUsusarios['idpermission'] == 13
                                            or $listaUsusarios['idpermission'] == 14 ){ ?>
                                            <input checked type="checkbox" name="reserva" value="reserva">
                                        <?php } else {?>
                                            <input type="checkbox" name="reserva" value="reserva">
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 pull-right">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" disabled placeholder="Mapa de Serviço">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <?php if( $listaUsusarios['idpermission'] == 2 or $listaUsusarios['idpermission'] == 1 or $listaUsusarios['idpermission'] == 6 or
                                            $listaUsusarios['idpermission'] == 4 or $listaUsusarios['idpermission'] == 3 or $listaUsusarios['idpermission'] == 5
                                            or $listaUsusarios['idpermission'] == 11 or $listaUsusarios['idpermission'] == 12 or $listaUsusarios['idpermission'] == 13
                                            or $listaUsusarios['idpermission'] == 14) { ?>
                                            <input checked type="checkbox" name="mapa" value="mapa">
                                        <?php } else {?>
                                            <input type="checkbox" name="mapa" value="mapa">
                                        <?php } ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 pull-left">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" disabled placeholder="Financeiro(Dar Baixa)">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <?php if( $listaUsusarios['idpermission'] == 7  ){ ?>
                                            <input checked type="checkbox" name="financeiro" value="financeiro">
                                        <?php } else {?>
                                            <input type="checkbox" name="financeiro" value="financeiro">
                                        <?php } ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 pull-right">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" disabled placeholder="Gerar apenas recibo">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <?php if( $listaUsusarios['idpermission'] == 10 ){ ?>
                                            <input type="checkbox" checked name="caixa" value="caixa">
                                        <?php } else {?>
                                            <input type="checkbox" name="caixa" value="caixa">
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 pull-left">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" disabled placeholder="Permitir Pagar Comissão">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <?php if( $listaUsusarios['idpermission'] == 2  or $listaUsusarios['idpermission'] == 4 or
                                            $listaUsusarios['idpermission'] == 3 or $listaUsusarios['idpermission'] == 14 ){ ?>
                                            <input checked type="checkbox" name="pcomissao" value="pcomissao">
                                        <?php } else {?>
                                            <input type="checkbox" name="pcomissao" value="pcomissao">
                                        <?php } ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 pull-right">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" disabled placeholder="Permitir Folha de Rosto">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <?php if( $listaUsusarios['idpermission'] == 2  or $listaUsusarios['idpermission'] == 4 or
                                            $listaUsusarios['idpermission'] == 3 or $listaUsusarios['idpermission'] == 13 ){ ?>
                                            <input checked type="checkbox" name="folha" value="folha">
                                        <?php } else {?>
                                            <input type="checkbox" name="folha" value="folha">
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 pull-left">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" disabled placeholder="Faturamento">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <?php if( $listaUsusarios['idpermission'] == 2 ){ ?>
                                            <input checked type="checkbox" name="gerente" value="gerente">
                                        <?php } else {?>
                                            <input type="checkbox" name="gerente" value="gerente">
                                        <?php } ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 pull-right">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" disabled placeholder="Administrador">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <?php if( $listaUsusarios['idpermission'] == 2 ){ ?>
                                            <input checked type="checkbox" name="administrador" value="administrador">
                                        <?php } else {?>
                                            <input type="checkbox" name="administrador" value="administrador">
                                        <?php } ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 pull-left">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" disabled placeholder="Pousada">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <?php if( $listaUsusarios['idpermission'] == 8 ){ ?>
                                            <input checked type="checkbox" name="pousada" value="pousada">
                                        <?php } else {?>
                                            <input type="checkbox" name="pousada" value="pousada">
                                        <?php } ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 pull-right">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" disabled placeholder="Revendedor">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <?php if( $listaUsusarios['idpermission'] == 9 ){ ?>
                                            <input checked type="checkbox" name="revendedor" value="revendedor">
                                        <?php } else {?>
                                            <input type="checkbox" name="revendedor" value="revendedor">
                                        <?php } ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 pull-left">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" disabled placeholder="Omitir Botão de Pagamento da Reserva">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <?php if( $listaUsusarios['idpermission'] == 11 ){ ?>
                                            <input checked type="checkbox" name="naopermitirpagamentoreserva" value="naopermitirpagamentoreserva">
                                        <?php } else {?>
                                            <input type="checkbox"  name="naopermitirpagamentoreserva" value="naopermitirpagamentoreserva">
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 pull-right">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" disabled placeholder="Permitir Relatório">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <?php if( $listaUsusarios['idpermission'] == 12 or $listaUsusarios['idpermission'] == 4 ){ ?>
                                            <input checked type="checkbox" name="financeirotwo" value="financeirotwo">
                                        <?php } else {?>
                                            <input type="checkbox" name="financeirotwo" value="financeirotwo">
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 container-fluid pull-left">
                            <input type="hidden" name="id" value="<?php echo( $listaUsusarios['id'] ); ?>">
                            <button type="submit" class="btn btn-outline-success btn-lg btn-block" name="atualizar">
                                Atualizar informações
                            </button>
                        </div>
                        <div class="col-lg-6 container-fluid pull-right">
                            <a href="./novo-usuario" class="btn btn-outline-primary btn-lg btn-block">
                                Voltar
                            </a>
                        </div>
                    </form>
                    <?php if( count( $registerDataUser ) > 0 ){ ?>
                        <?php foreach ($registerDataUser as $item){ ?>
                            <div class="col-md-6 pull-left">
                                <label>CPF / CNPJ:</label>
                                <input disabled class="form-control" type="text" value="<?php echo($item->cpf); ?>">
                            </div>
                            <div class="col-md-6 pull-right">
                                <label>Telefone:</label>
                                <input disabled class="form-control" type="text" value="<?php echo($item->telephone); ?>">
                            </div>

                            <div class="col-md-4 pull-left">
                                <label>Agência:</label>
                                <input disabled class="form-control" type="text" value="<?php echo($item->agency); ?>">
                            </div>
                            <div class="col-md-4 pull-left">
                                <label>Conta:</label>
                                <input disabled class="form-control" type="text" value="<?php echo($item->count); ?>">
                            </div>
                            <div class="col-md-4 pull-right">
                                <label>Banco:</label>
                                <input disabled class="form-control" type="text" value="<?php echo($item->banck); ?>">
                            </div>
                        <?php }?>
                        <div class="image">
                            <img src="./../images/<?php echo($listaUsusarios['img']); ?>" class="">
                        </div>
                    <?php }?>
                </div>

            </div>
        </div>
    </div>

<?php require_once ('footer.php'); ?>
