<?php

namespace gray\tecnicas\controller;

require_once('/home/shinobil/public_html/includes/functions_user.php');

class main
{
    /* @var \phpbb\config\config */
    protected $config;

    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\template\template */
    protected $template;

    /* @var \phpbb\user */
    protected $user;

    protected $db;
    protected $auth;

    /**
     * Constructor
     *
     * @param \phpbb\config\config      $config
     * @param \phpbb\controller\helper  $helper
     * @param \phpbb\template\template  $template
     * @param \phpbb\user               $user
     */
    public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, \phpbb\db\driver\driver_interface $db, \phpbb\auth\auth $auth)
    {
        $this->config   = $config;
        $this->helper   = $helper;
        $this->template = $template;
        $this->user     = $user;
        $this->db       = $db;
        $this->auth     = $auth;
    }

    /**
     * Demo controller for route /demo/{name}
     *
     * @param string $name
     * @throws \phpbb\exception\http_exception
     * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
     */
    public function handle()
    {
        $this->validate_access();

        return $this->helper->render('tecnicas/home.html', 'Administrador de SL');
    }

    public function createTecnicas()
    {
        $this->validate_access();

        $this->template->assign_vars(array(
                'RAMA_OPTIONS'	=> $this->get_rama_options(),
            ));

        return $this->helper->render('tecnicas/create.html', 'Administrador de SL');
    }

    public function store()
    {
        $this->validate_access();

        $tipo_array = request_var('tipo[]', array(0));

        //Tipos
        $tipos = $tipo_array;
        $tipos_texto = "";

          for ($i=0; $i < count($tipos); $i++) {
              switch ($tipos[$i]) {
              case 1:
                $tipos_texto .= "<tipo nin>Ninjutsu</tipo>";
                break;
              case 2:
                $tipos_texto .= "<tipo tai>Taijutsu</tipo>";
                break;
              case 3:
                $tipos_texto .= "<tipo gen>Genjutsu</tipo>";
                break;
              case 4:
                $tipos_texto .= "<tipo fuin>Fuinjutsu </tipo>";
                break;
              case 5:
                $tipos_texto .= "<tipo nin>Senjutsu</tipo>";
                break;
              case 6:
                $tipos_texto .= "<tipo biju>Bijūjutsu</tipo>";
                break;
              case 7:
                $tipos_texto .= "<rango>Kinjutsu</rango>";
                break;
              }
          }

        $tecnica = array(
            'ETIQUETA'          => utf8_normalize_nfc(request_var('etiqueta', '', true)),
            'NOMBRE'            => utf8_normalize_nfc(request_var('name', '', true)),
            'TIPO'              => $tipos_texto,
            'RANGO'             => utf8_normalize_nfc(request_var('rango', '', true)),
            'SELLOS'            => utf8_normalize_nfc(request_var('sellos', '', true)),
            'REQUISITOS'        => utf8_normalize_nfc(request_var('requisitos', '', true)),
            'EFECTOS'           => utf8_normalize_nfc(request_var('efectos', '', true)),
            'DAMAGE_PV'         => (int) request_var('pv_damage', 1),
            'DAMAGE_PC'         => (int) request_var('pc_damage', 1),
            'DAMAGE_STA'        => (int) request_var('sta_damage', 1),
            'DAMAGE_TURNO'      => (int) request_var('por_turno_damage', 1),
            'COSTE_PV'          => (int) request_var('pv', 1),
            'COSTE_PC'          => (int) request_var('pc', 1),
            'COSTE_STA'         => (int) request_var('sta', 1),
            'COSTE_TURNO'       => (int) request_var('por_turno', 1),
            'DESCRIPCION'       => utf8_normalize_nfc(request_var('descripcion', '', true)),
        );

        // Coste en Puntos de Aprendizaje
        $coste_tecnica = $this->get_coste_tecnica($tecnica['RANGO']);

        $rama = (int) request_var('rama', 1);
        $etiqueta = $tecnica['ETIQUETA'];
        $etiqueta = "[".$etiqueta."][/".$etiqueta."]";
        $etiqueta_envio = $tecnica['ETIQUETA'];

        $nombre = "<nombre>".$tecnica['NOMBRE']."</nombre>";
        $rango = "<rango>RANGO ".$tecnica['rango']."</rango>";
        $sellos = $tecnica['SELLOS'];
        $descripcion = $tecnica['DESCRIPCION'];

        //Daño
        $damage = "";
        $contador = 0;
        if ($tecnica['DAMAGE_PV'] != "") {
            $damage .= $tecnica['DAMAGE_PV']." PV";
            $contador++;
        }
        if ($tecnica['DAMAGE_STA'] != "") {
            if ($contador != 0) {
                $damage .= ", ".$tecnica['DAMAGE_STA']." STA";
                $contador++;
            } else {
                $damage .= $tecnica['DAMAGE_STA']." STA";
                $contador++;
            }
        }
        if ($tecnica['DAMAGE_PC'] != "") {
            if ($contador != 0) {
                $damage .= ", ".$tecnica['DAMAGE_PC']." PC";
                $contador++;
            } else {
                $damage .= $tecnica['DAMAGE_PC']." PC";
                $contador++;
            }
        }
        if (isset($tecnica['DAMAGE_TURNO'])) {
            $tecnica['DAMAGE_TURNO'] = true;
            $damage .= $damage." por turno.";
        } else {
            $tecnica['DAMAGE_TURNO'] = false;
        }


        //Coste
        $coste = "";
        $contador = 0;

        if ($tecnica['COSTE_PC'] != "") {
            $coste .= $tecnica['COSTE_PC']." PC";
            $contador++;
        }

        if ($tecnica['COSTE_STA'] != "") {
            if ($contador != 0) {
                $coste .= ", ".$tecnica['COSTE_STA']." STA";
                $contador++;
            } else {
                $coste .= $tecnica['COSTE_STA']." STA";
                $contador++;
            }
        }

        if ($tecnica['COSTE_PV'] != "") {
            if ($contador != 0) {
                $coste .= ", ".$tecnica['COSTE_PV']." PV";
                $contador++;
            } else {
                $coste .= $tecnica['COSTE_PV']." PV";
                $contador++;
            }
        }

        if (isset($tecnica['COSTE_TURNO'])) {
            $tecnica['COSTE_TURNO'] = true;
            $coste .= $coste." por turno.";
        } else {
            $tecnica['COSTE_TURNO'] = false;
        }

        //Requisitos
        $requisitos = $tecnica['REQUISITOS'];
        $requisitos = str_replace("/#", "</ul>", $requisitos);
        $requisitos = str_replace("/$","</li>", $requisitos);
        $requisitos = str_replace("/&","</pv>", $requisitos);
        $requisitos = str_replace("/@","</pc>", $requisitos);
        $requisitos = str_replace("/|","</sta>", $requisitos);
        $requisitos = str_replace("#","<ul>", $requisitos);
        $requisitos = str_replace("$","<li>", $requisitos);
        $requisitos = str_replace("&","<pv>", $requisitos);
        $requisitos = str_replace("@","<pc>", $requisitos);
        $requisitos = str_replace("|","<sta>", $requisitos);

        //Efectos
        $efectos = $tecnica['EFECTOS'];
        $efectos = str_replace("/#", "</ul>", $efectos);
        $efectos = str_replace("/$","</li>", $efectos);
        $efectos = str_replace("/&","</pv>", $efectos);
        $efectos = str_replace("/@","</pc>", $efectos);
        $efectos = str_replace("/|","</sta>", $efectos);
        $efectos = str_replace("#","<ul>", $efectos);
        $efectos = str_replace("$","<li>", $efectos);
        $efectos = str_replace("&","<pv>", $efectos);
        $efectos = str_replace("@","<pc>", $efectos);
        $efectos = str_replace("|","<sta>", $efectos);

        $texto_tecnica = "<jutsu>";
        $texto_tecnica .= $nombre.$tipos_texto.$rango."<datos><b>Requisitos:</b>".$requisitos." </br><b>Sellos:</b>".$sellos."<br/> <b>Efectos:</b>".$efectos;
        if ($damage != "") {
            $texto_tecnica .= " <b>Daño:</b>".$damage."<br/>";
        }
        $texto_tecnica .= "<b>Coste:</b>".$coste."</datos><desc>".$descripcion."</desc><hr/><c><b onclick='selectCode(this)'>Código:</b><code>".$etiqueta."</code></c>";
        $texto_tecnica .= "</jutsu>";

        $this->crearBbcode($texto_tecnica, $etiqueta_envio, $tecnica, $rama);
    }

    public function crearBbcode($codigo, $etiqueta, $tecnica, $ramaTec)
    {

      $sql = 'SELECT MAX(bbcode_id) as last_id
  			FROM ' . BBCODES_TABLE;
  		$result = $this->db->sql_query($sql);
  		$bbcode_id = (int) $this->db->sql_fetchfield('last_id');
  		$this->db->sql_freeresult($result);
  		$bbcode_id += 1;

      if ($this->get_coste_tecnica($tecnica['RANGO']) == 0) {
        $coste = 0;
      }
      else{
        $coste = $this->get_coste_tecnica($tecnica['RANGO']);
      }
      $rama = array(
          'nombre'    => $tecnica['NOMBRE'],
          'rango'     => $tecnica['RANGO'],
          'etiqueta'  => $tecnica['ETIQUETA'],
          'coste'     => $coste,
          'rama_id'   => $ramaTec,
      );

        $sql_array_bbcode = array(
        'bbcode_id'           => $bbcode_id,
        'bbcode_tag'          => $etiqueta,
        'bbcode_match'        => "[".$etiqueta."][/".$etiqueta."]",
        'bbcode_tpl'          => $codigo,
        'first_pass_match'    => "!\[".$etiqueta."\]\[/".$etiqueta."\]!i",
        'first_pass_replace'  => "[".$etiqueta.":\$uid][/".$etiqueta.":\$uid]",
        'second_pass_match'   => "[".$etiqueta.":\$uid][/".$etiqueta.":\$uid]",
        'second_pass_replace' => "",
      );

    		$this->db->sql_freeresult($query_max);
        $sql = "INSERT INTO ". BBCODES_TABLE . $this->db->sql_build_array('INSERT', $sql_array_bbcode);
        $this->db->sql_query($sql);

        $sql = "INSERT INTO ". TECNICAS_INFO . $this->db->sql_build_array('INSERT', $tecnica);
        $this->db->sql_query($sql);

        // if ($rama['rama_id'] != 0) {
          $sql = "INSERT INTO ". TECNICAS_TABLE . $this->db->sql_build_array('INSERT', $rama);
          $this->db->sql_query($sql);
        // }
    }

    public function get_coste_tecnica($rango)
    {
      switch ($tecnica['RANGO']) {
        case 'E':
          $coste_tecnica = 0;
          return $coste_tecnica;
        break;
        case 'D':
          $coste_tecnica = 1;
          return $coste_tecnica;
        break;
        case 'C':
          $coste_tecnica = 2;
          return $coste_tecnica;
        break;
        case 'B':
          $coste_tecnica = 3;
          return $coste_tecnica;
        break;
        case 'A':
          $coste_tecnica = 5;
          return $coste_tecnica;
        break;
        case 'S':
          $coste_tecnica = 10;
          return $coste_tecnica;
        break;
      }

    }
    public function get_rama_options()
    {
        $options = "";

        $query = $this->db->sql_query("SELECT rama_id, nombre FROM " . RAMAS_TABLE);

        while ($row = $this->db->sql_fetchrow($query)) {
            $options .= "<option value='" . $row['rama_id'] . "'>" . $row['nombre'] . "</option>";
        }
        $this->db->sql_freeresult($query);

        return $options;
    }

    public function validate_access()
    {
        $grupo = $this->user->data['group_id'];
        if ($grupo != 5) {
            trigger_error("No puedes acceder a esta sección.");
        }
    }

    public function get_return_link($view)
    {
        return "<br /><a href='/tecnicas/$view'>Volver</a>.";
    }
}