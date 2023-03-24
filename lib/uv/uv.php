<?php

//////////////////////////////////////////////////////////////////////////////////////
////    UV
//////////////////////////////////////////////////////////////////////////////////////

function file_get_contents_utf8($fn) {
     $content = file_get_contents($fn);
      return mb_convert_encoding($content, 'UTF-8',
          mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
}


function mail_utf8($to, $from_user, $from_email, 
                                             $subject = '(No subject)', $message = '')
   { 
      $from_user = "=?UTF-8?B?".base64_encode($from_user)."?=";
      $subject = "=?UTF-8?B?".base64_encode($subject)."?=";

      $headers = "From: $from_user <$from_email>\r\n". 
                 "MIME-Version: 1.0" . "\r\n" . 
                 "Content-type: text/html; charset=UTF-8" . "\r\n"; 

     return mail($to, $subject, $message, $headers); 
   }

function envia_email_externo ($email) {
        global $DB; 
        $contuser=0;
        #$de = exec("whoami")."@".exec("hostname -f");
        $de = "aula-virtual-tech@uv.es";
        $para = $email;
        $asunto = "Compte migrat a la nova Aula Virtual (Moodle)";
        $cabeceras = "From: $de <$de>\r\n".
                     "Reply-To: aula.virtual.tech@uv.es\r\n".
                     "MIME-Version: 1.0" . "\r\n" . 
                     "Content-type: text/plain; charset=UTF-8" . "\r\n";
        $sql = "SELECT password FROM mdl_user where email = '$email'";
        $users = $DB->get_records_sql($sql); 
        echo("DRG procesa la contrasenya\n");
        foreach ($users as $user) {
             $contuser++;
             $mensaje.="Degut a que la comunitat a la cual pertanyeu ha sigut migrada a la nova Aula Virtual basada en Moodle (https://aulavirtual.uv.es).
  S'ha restableit la seua contrasenya:
   - compte     : $email
   - contrasenya: $user->password \n";
             $mensaje.="Per a qualsevol dubte o aclariment, recordeu que podeu consultar la FAQ (http://ir.uv.es/krTz3fo), o contactar amb el CAU (https://solicitudes.uv.es).\n";
             $mensaje.="-----------------------------------------------------------------------------------------------------------------------------------------\n";
             $mensaje.="Debido a que la comunidad a la cual pertenecéis ha sido migrada en la nueva Aula Virtual basada en Moodle (https://aulavirtual.uv.es).
  Se ha restablecido su contraseña:
   - cuenta     : $email
   - contraseXa : $user->password \n";
             $mensaje.="Para cualquier duda o aclaración, recordad que podéis consultar la FAQ (http://ir.uv.es/krTz3fo), o contactar con el CAU (https://solicitudes.uv.es).\n";
             echo("DRG enviando la contraseña\n");
             return mail($para, $asunto, $mensaje, $cabeceras);
        }
        if ($contuser < 0 ) {
             echo("DRG no se envia la contraseña\n");
             return;
        }
 
}

function envia_email_migracion ($dotlrnid,$moodleid) {
        global $DB;
        #if ($CFGTRASPASO->traspaso_enviaremail!='1') return;
        #$de = exec("whoami")."@".exec("hostname -f");
        $de = "aula.virtual.tech@uv.es";
        $rol = manager;
        $sql ="SELECT usr.email
        FROM mdl_course c
        INNER JOIN mdl_context cx ON c.id = cx.instanceid
        AND cx.contextlevel = '50'
        INNER JOIN mdl_role_assignments ra ON cx.id = ra.contextid
        INNER JOIN mdl_role r ON ra.roleid = r.id
        INNER JOIN mdl_user usr ON ra.userid = usr.id
        WHERE c.id = $moodleid and r.shortname = '$rol'
        ORDER BY usr.email";
        $arrayadmins = $DB->get_records_sql($sql);
        foreach ($arrayadmins as $admin) {
                  $para.=$admin->email;   
                  $para.=",";
        }
        $para = trim($para, ',');
        echo("DRG end $end para $para \n");
        
        $asunto = "Comunitat migrada a Aula Virtual (Moodle)";
        $cabeceras = "From: $de <$de>\r\n".
                     "Reply-To: aula.virtual.tech@uv.es\r\n".
                     "MIME-Version: 1.0" . "\r\n" . 
                     "Content-type: text/plain; charset=UTF-8" . "\r\n";
        $sql = "SELECT shortname FROM mdl_course where id = ? ";
        $names = $DB->get_records_sql($sql, array($moodleid));  
        foreach ( $names as $r ) {
              $name = $r->shortname;
        }
        $mensaje.= "La següent comunitat ha sigut migrada a Moodle:\n";
        $mensaje.="-";
        $mensaje.= $name;
        $mensaje.="\n";  
        $mensaje.="-";   
        $mensaje.= "https://aulavirtual.uv.es/course/view.php?id=$moodleid \n";
        $mensaje.= "Recuerdeu fins el 30 de gener de 2017 podreu accedir a la antigua comunitat a dotLRN (https://dotlrn.uv.es), per comprovar que la migració s'ha realitzat correctament.
Una vegada acabe aquest termini, la comunitat quedarà arxivada. Despres de ser arxivades, les comunitats només seran accesibles sota sol·licitud i de manera temporal.
Recordeu avisar de la migració a la resta dels membres de la comunitat.
Per a qualsevol dubte o aclariment, recordeu que podeu consultar la FAQ (http://ir.uv.es/krTz3fo), o contactar amb el CAU (https://solicitudes.uv.es). ";
        $mensaje.= "------------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n";
        $mensaje.= "La siguiente comunidad ha sido migrada a Moodle:\n";
        $mensaje.="-";
        $mensaje.= $name;
        $mensaje.="\n";  
        $mensaje.="-";   
        $mensaje.= "https://aulavirtual.uv.es/course/view.php?id=$moodleid \n";
        $mensaje.= "Recordad hasta el 30 de enero de 2017 podréis acceder a la antigua comunidad en dotLRN (https://dotlrn.uv.es), para comprobar que la migración se ha realizado correctamente.
Una vez finalice el plazo, la comunidad quedará archivada. Una vez archivadas, las comunidades solamente serán accesibles bajo soliciutd y de manera temporal.
Recordar avisar de la migración al resto de los miembros de la comunidad.                         
Para cualquier duda o aclaración, recordad que podéis consultar la FAQ (http://ir.uv.es/krTz3fo), o contactar con el CAU (https://solicitudes.uv.es).";
        //echo "mail($para, $asunto, $mensaje, $cabeceras)";
        return mail($para, $asunto, $mensaje, $cabeceras);  
}


function uv_get_curl2 ($url) {
  $context = stream_context_create(
    array(
        'http'  => array('follow_location' => 1), 
        'https' => array('follow_location' => 1)
    )
  );

  if (($output = file_get_contents ($url, false, $context)) === FALSE) {
    echo "ERROR en file_get_contents '$url'\n";
  }
  return $output;  
}

function enrol_user_uv($courseid, $userid, $roleid, $enrolmethod = 'manual') {
    global $DB;
    $user = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $context = context_course::instance($course->id);
    if (!is_enrolled($context, $user)) {
        $enrol = enrol_get_plugin($enrolmethod);
        if ($enrol === null) {
            return false;
        }
        $instances = enrol_get_instances($course->id, true);
        $manualinstance = null;
        foreach ($instances as $instance) {
            if ($instance->name == $enrolmethod) {
                $manualinstance = $instance;
                break;
            }
        }
        if ($manualinstance !== null) {
            $instanceid = $enrol->add_default_instance($course);
            if ($instanceid === null) {
                $instanceid = $enrol->add_instance($course);
            }
            $instance = $DB->get_record('enrol', array('id' => $instanceid));
        }
        $enrol->enrol_user($instance, $userid, $roleid);
    }
    return true;
}


/*function uv_ns_httpget ($url,$timeout,$depth)

  {  
   $contenturl = "";
   #DRG para activar/desactivar las llamadas a bancuv;
   # update uv_parameters set parameter_value = '1' where parameter_name = 'bancuv';
   #select * from uv_parameters where parameter_name = 'bancuv';
##   $BANCUV_ENABLED = [db_string get_parameter_value {select parameter_value from uv_parameters where parameter_name = 'bancuv'} -default "0"];
   #set BANCUV_ENABLED 0   
   #ns_log Notice "DRG ::uvtools::uv_ns_httpget BANCUV_ENABLED $BANCUV_ENABLED"
   if ($BANCUV_ENABLED == 0) {
    #return 1
    #ns_log Notice "DRG ::uvtools::uv_ns_httpget2 BANCUV_ENABLED $BANCUV_ENABLED"
    break;
   } else {
    if ([catch {set contenturl [ns_httpget $url $timeout $depth]} result]) {
      #ERROR
##      ns_log Notice [format "ERROR EN uv_ns_httpget $url"]
      #set contenturl "Temporalment fora de servei"
      #return $contenturl
      break;
    } else {
      #NO ERROR
      #ns_log Notice "NO ERROR contenturl=$contenturl result=$result url=$url timeout=$timeout depth=$depth"
    }
    return $contenturl
   }  
  }*/


function uv_get_curl ($url) {
  global $DB;
  $options = array( 
      CURLOPT_RETURNTRANSFER => true,     // return web page 
      CURLOPT_HEADER         => false,    // do not return headers 
      CURLOPT_FOLLOWLOCATION => true,     // follow redirects 
      CURLOPT_USERAGENT      => "spider", // who am i 
      CURLOPT_AUTOREFERER    => true,     // set referer on redirect 
      CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect 
      CURLOPT_TIMEOUT        => 120,      // timeout on response 
      CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects 
  ); 
  #ACTIVA update mdl_uv_config set valor = 1 where nombre = 'bancuv';
  $source = "bancuv";
  $BANCUV_ENABLED=$DB->get_field("uv_config","valor",array('nombre'=>$source));
  #m("BANCUV STATE $BANCUV_ENABLED");
  if ($BANCUV_ENABLED == 0) {
    return 0;
  } else {
    $ch      = curl_init( $url ); 
    curl_setopt_array( $ch, $options ); 
    $content = curl_exec( $ch ); 
    $err     = curl_errno( $ch ); 
    $errmsg  = curl_error( $ch ); 
    $header  = curl_getinfo( $ch ); 
    curl_close( $ch ); 
    return $content;
  }
}

// 0  3   7     13 16
// c15c009a34444gARsL01
// https://dotlrn.uv.es/dotlrn/classes/c009/34444/c15c009a34444gAR/one-community?page_num=0
// http://bancuv.uv.es/pls/uv0/oca.muestra_asignatura?lang=es_ES&cod=c009.34444&tipo=A&enviarcab=NO&cacad=2015
// CAACnnnammmmmGgg
//             --- C         fija
//             --- AA        curso
//             --- C            Tlugar
//             --- nnn          Lugar
//             --- a         Será una 'a' siempre
//             --- mmmmm        Modulo
//             --- G            fija
//             --- gg        Grupo
function parsea_currutaca ($currutaca) {
  // TCL if { [regexp {c0[0-9](.+)a(.+)g(.+)} $commkey match centro asig grupo]} {  
  $uno    = substr ($currutaca, 0, 3);   $curso      = substr ($uno, 1, 2);     //15 
  $dos    = substr ($currutaca, 3, 4);   $centro     = $dos;                    //c009
  $tres   = substr ($currutaca, 7, 6);   $asignatura = substr ($tres, 1, 5);    //34444
  $cuatro = substr ($currutaca, 13, 3);  $grupo      = substr ($cuatro, 1, 2);  //AR
  $cinco  = substr ($currutaca, 16, 4);  $subgrupo   = substr ($cinco, 1, 3);   //L01
  $year   = "20" . $curso;                                                      //2015
  $data = array ("currutaca"=>$currutaca, "curso"=>$curso, "centro"=>$centro, "asignatura"=>$asignatura, "grupo"=>$grupo, "subgrupo"=>$subgrupo, "year"=>$year);
  return $data;
}
function get_community_key ($cid) {
  global $DB, $CFG;
  if (!$course = $DB->get_record("course", array('id' => $cid))) {error("Course is misconfigured");}
  return $course->idnumber;
}

// https://webges.uv.es/uvGuiaDocenteWeb/guia?APP=uvGuiaDocenteWeb&ACTION=MOSTRARGUIA.M&MODULO=33124&CURSOACAD=2015&IDIOMA=C
function getUrlGuiaDocente ($asignatura,$year) {
  global $DB,$CFG;
  #if (!$course = $DB->get_record("course", array('id' => $cid))) {error("Course is misconfigured");}
  //Falta coger el language del user
  $lang = current_language();
  if ($lang == "ca") {$xlang = "V";} elseif ($lang == "en") {$xlang = "I";} elseif ($lang == "es") {$xlang = "C";}
  #$currutaca = $course->idnumber;
  #$data = array ();
  #$data = parsea_currutaca ($currutaca);
  #$asignatura = $data['asignatura'];
  #$year =  $data['year'];
  $source = "bancuv";
  $BANCUV_ENABLED=$DB->get_field("uv_config","valor",array('nombre'=>$source));
  #m("BANCUV STATE $BANCUV_ENABLED");
  if ($BANCUV_ENABLED == 0) {
    $url = "#";
  } else {
    $url = "https://webges.uv.es/uvGuiaDocenteWeb/guia?APP=uvGuiaDocenteWeb&ACTION=MOSTRARGUIA.M&MODULO=$asignatura&CURSOACAD=$year&IDIOMA=$xlang";
  }
  return $url;
}
function getUrlEncuesta($npa,$asignatura,$grupo) {
   //DRG 
   //bancuv.uv.es/pls/uv0/paradotcen.numpar_any?wany=2016
   #$url =  "http://bancuv.uv.es/pls/uv0/paramoodle.enc_evaluacion_alumno?wnpa=PJ34959\&wasignatura=33242\&wgrupo=A";
   $url =  "http://bancuv.uv.es/pls/uv0/paramoodle.enc_evaluacion_alumno?wnpa=$npa&wasignatura=$asignatura&wgrupo=$grupo&wdetalle=2";
   $urlData = uv_get_curl($url);
   #Bernabeu Auban, Juan
   #PENDIENTE                
   $urlreturn = "NoDataFound";
   if ($urlData == "No data found") {
      $urlreturn = "NoDataFound";
   } else {
      #DRG si hay url es que hay alguna encuesta pendiente
      if (preg_match("/PENDIENTE/",$urlData)) {
          $urlreturn = "https://secvirtual.uv.es/pls/uv0/encuestas.selecciona_profesor";
      } else {
          $urlreturn = "NoDataFound";
      }
   }
   #devuelve 0 en caso de error
   return $urlreturn;
}
function ma ($title, $a) {
  echo "<B>$title</B>\n";
  echo "<pre>\n";
  print_r($a);
  echo "</pre>\n";
}

function m ($linea) {
  syslog (LOG_NOTICE, $linea);
}

function uv_get_photo ($username, $onlyurl = 1, $class = "userpicture defaultuserpic", $height = "80px", $width = "80px", $title = "", $alt = "") {
  global $DB, $CFG;
  $value = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : '';
  $httpprotocol = strpos(strtolower($value),'https') === FALSE ? 'http' : 'https';
  $user = $DB->get_record ('user', array('username'=>$username), '*', MUST_EXIST);
  if ($user->auth == 'manual') {
    $urlphoto = $CFG->wwwroot . '/user/pix.php?file=/' . $user->id . '/f1.jpg" width="80px" height="80px" title="' . $user->firstname . ' ' . $user->lastname . '" alt="' . $user->firstname . ' ' . $user->lastname;
    if ($onlyurl == 1) {
      return $urlphoto;
    } else {
      $img = '<IMG SRC="' . $urlphoto . '" CLASS="' . $class . '" HEIGHT="' . $height . '" WIDTH="' . $width . '" HEIGHT="' . $height . '" TITLE="' . $title . '" ALT="' . $alt . '"/>';
      return $img;
    }  
  } else {
    $url =  "https://webservsec.uv.es/getPhotoUrlFromUsername?env=&user_name=$username&mode=plain";
    $urlphoto = uv_get_curl ($url);
    if ($httpprotocol == "https") {
      str_replace("http://", "https://", $urlphoto);
    } else  {
      str_replace("https://", "http://", $urlphoto);
    }
    if ($onlyurl == 1) {            
      return $urlphoto;
    } else {
      $img = '<IMG SRC="' . $urlphoto . '" CLASS="' . $class . '" HEIGHT="' . $height . '" WIDTH="' . $width . '" HEIGHT="' . $height . '" TITLE="' . $title . '" ALT="' . $alt . '"/>';
      return $img;
    }
  }  
}

function uv_get_photo_by_id ($userid, $onlyurl = 1, $class = "userpicture defaultuserpic", $height = "80px", $width = "80px", $title = "", $alt = "") {
  global $DB;
  $username = $DB->get_field('user','username', array('id'=>$userid));  
  return uv_get_photo ($username, $onlyurl, $class, $height, $width, $title, $alt);
}

function getNumparFromCacadFromBancuv($cacad) {
   //DRG 
   //bancuv.uv.es/pls/uv0/paradotcen.numpar_any?wany=2016
   $url =  "http://bancuv.uv.es/pls/uv0/paradotcen.numpar_any?wany=$cacad";
   $urlreturn = uv_get_curl($url);
   #devuelve 0 en caso de error
   return $urlreturn;
}
function getYearFromNumpar($numpar,$formatYearFull) {
   //DRG coger la fórmula año modulo ....
   if ($numpar == "1") {
      if ($formatYearFull == "Y") {
        return "2018-19";
      } elseif ($formatYearFull == "B") {
        return "2018";
      } else {
        return "19";
      }
   }   
   if ($numpar == "2") {
      if ($formatYearFull == "Y") {
        return "2019-20";
      } elseif ($formatYearFull == "B") {
        return "2019";
      } else {
        return "20";
      }
   }   
   if ($numpar == "3") {
      if ($formatYearFull == "Y") {
        return "2020-21";
      } elseif ($formatYearFull == "B") {
        return "2020";
      } else {
        return "21";
      }
   }   
   if ($numpar == "4") {
      if ($formatYearFull == "Y") {
        return "2016-17";
      } elseif ($formatYearFull == "B") {
        return "2016";
      } else {
        return "17";
      }
   }   
   if ($numpar == "5") {
      if ($formatYearFull == "Y") {
        return "2017-18";
      } elseif ($formatYearFull == "B") {
        return "2017";
      } else {
        return "18";
      }
   }   
}
function getYearFromCacad($cacad) {
   //DRG - ya no se usa ahora getNumparFromCacadFromBancuv
   if ($cacad == "2018-19") {
           return "1";
   }
   if ($cacad == "2019-20") {
        return "2";
   }
   if ($cacad == "2020-21") {
        return "3";
   }
   if ($numpar == "2016-17") {
        return "4";
   }
   if ($numpar == "2017-18") {
        return "5"; 
   }
}   
function getYearBeginFromCacad($cacad) {
   //DRG - ya no se usa ahora getNumparFromCacadFromBancuv
   
   if ($cacad == "2018-19") {
        return "2018";
   }
   if ($cacad == "2019-20") {
        return "2019";
   }
   if ($cacad == "2020-21") {
        return "2020";
   }
   if ($numpar == "2016-17") {
        return "2016";
   }
   if ($numpar == "2017-18") {
        return "2017"; 
   }
}

/**
 * Check that an email is allowed.  It returns an error message if there was a problem.
 *
 * @param string $email Content of email
 * @return string|false
 */
function uv_email_is_not_allowed($email) {
    $denyemailaddresses="uv.es alumni.uv.es ext.uv.es valencia.edu alumni.valencia.edu ext.valencia.edu fundacions.uv.es cdi.uv.es fundacions.valencia.edu cdi.valencia.edu";
    #DRG $denyemailaddresses="uv.es valencia.edu ";
    if (!empty($denyemailaddresses)) {
        $denied = explode(' ', $denyemailaddresses);
        foreach ($denied as $deniedpattern) {
            $deniedpattern = trim($deniedpattern);
            if (!$deniedpattern) {
                continue;
            }
            if (strpos($deniedpattern, '.') === 0) {
                if (strpos(strrev($email), strrev($deniedpattern)) === 0) {
                    // Subdomains are in a form ".example.com" - matches "xxx@anything.example.com".
                    return get_string('emailnotallowed', '', $denyemailaddresses);
                }

            } else if (strpos(strrev($email), strrev('@'.$deniedpattern)) === 0) {
                return get_string('emailnotallowed', '', $denyemailaddresses);
            }
        }
    }
    return false;
}

/**
 * Devuelve una carpeta o la crea.
 *
 * @param $courseid id del curso.
 * @param $resource_name nombre de la carpeta.
 *
 * @return context de la carpeta
 */
function get_folder($courseid, $resource_name, $sectionName) {
    global $DB, $CFG;

        //Comprobamos si la carpeta ya existe ya existe

        /*$sql = "SELECT cm.id as cmid FROM {course_modules} cm, {folder} res
        WHERE res.name = '" . $resource_name . "'
        AND cm.course = " . $courseid . "
        AND cm.instance = res.id";*/

        $sql = "SELECT cm.id as cmid FROM {course_modules} cm, {folder} res, {course_sections} cs
        WHERE res.name = '" . $resource_name . "'
        AND cm.course = " . $courseid . "
                AND cs.name = '" . $sectionName . "'
                AND cs.course = cm.course
        AND cm.instance = res.id
                AND cm.section = cs.id";

    if (! $coursemodule = $DB->get_record_sql($sql)) {
        require_once($CFG->dirroot.'/course/lib.php');

        //m("Create new folder");

                $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

                // get module id
                $module = $DB->get_record('modules', array('name' => 'folder'), '*', MUST_EXIST);

                // get course section
                /*course_create_sections_if_missing($course->id, 0);
        $modinfo = get_fast_modinfo($course->id);
                $cw = $modinfo->get_section_info(0);
                */
                m("DRG name $sectionName");
                $sectionid = $DB->get_record('course_sections', array('course' => $course->id, 'name' => $sectionName), '*', MUST_EXIST);
                //DRG si no existe Recursos lo ponemos en Tema 1
                //if (!$sectionid) {
                //  $section="1";
                //  $sectionid = $DB->get_record('course_sections', array('course' => $course->id, 'section' => $section), '*', MUST_EXIST); 
                //}
                $folder_data = new stdClass();
                $folder_data->course = $course->id;
                $folder_data->name = $resource_name;
                $folder_data->intro = '<p>'.$resource_name.'</p>';
                $folder_data->introformat = 1;
                $folder_data->revision = 1;
                $folder_data->timemodified = time();
                $folder_data->display = 0;
                $folder_data->showexpanded = 0;
                $folder_data->showdownloadfolder = 1;

                $folder_id = $DB->insert_record('folder', $folder_data);

                //m("folder id: " . $folder_id);

                // add course module
                $cm = new stdClass();
                $cm->course = $courseid;
                $cm->module = $module->id; // should be retrieved from mdl_modules
                $cm->instance = $folder_id; // from mdl_resource
                $cm->section = $sectionid->id; // from mdl_course_sections
                $cm->visible = 1;
                $cm->visibleold = 1;
                $cm->showavailability = 1;
                $cm->added = time();

                $cmid = $DB->insert_record('course_modules', $cm);

                //m("course module id: " . $cmid);

                // add module to course section so it'll be visible
                if ($DB->record_exists('course_sections', array('course' => $courseid, 'name' => $sectionName))) {
                        $sectionid = $DB->get_record('course_sections', array('course' => $courseid, 'name' => $sectionName));

                        // if sequence is not empty, add another course_module id
                        if (!empty($sectionid->sequence)) {
                                $sequence = $sectionid->sequence . ',' . $cmid;
                        } else {
                                // if sequence is empty, add course_module id
                                $sequence = ''.$cmid;
                        }

                        $course_section = new stdClass();
                        $course_section->id = $sectionid->id;
                        $course_section->course = $courseid;
                        $course_section->section =  $sectionid->section;
                        $course_section->sequence = $sequence;
                        $csid = $DB->update_record('course_sections', $course_section);

                } else {

                        $sequence = ''.$cmid;

                        $course_section = new stdClass();
                        $course_section->course = $courseid;
                        $course_section->section = $sectionid->section;
                        $course_section->sequence = $sequence;

                        $csid = $DB->insert_record('course_sections', $course_section);
                }

                rebuild_course_cache($courseid, true);

                // get context again, this time with all resources present
                $context = get_folder($courseid, $resource_name, $sectionName);
                return $context;

    } else {
        $context = context_module::instance($coursemodule->cmid);

        return $context;
    }
} // get_folder



//////////////////////////////////////////////////////////////////////////////////////
////    UC3M
//////////////////////////////////////////////////////////////////////////////////////

function url_foto_tarin (&$user) {
        global $DB;

        //$src= "https://aplicaciones.uc3m.es/fotoTarin/obtenerFoto.uc3m?i=12352&r=4423c5994774cd881de7ae52616d8bc9";

        if (!isset($user->username)) $user->username = $DB->get_field('user','username',array('id'=>$user->id));

        if (is_numeric($user->username)) { # usuarios con IDU
                $idu=$user->username;
                         
                $clave="clave,muy,segura.";
                        
                $cadena = $idu.$clave;
                $hash = md5($cadena);
                $src = "https://aplicaciones.uc3m.es/fotoTarin/obtenerFoto.uc3m?i=$idu&r=$hash";
        } else { # cuenta admin y otras cuentas locales
                $src = false;
        }
        
        return $src;
        
}


/**
 * Omar: Returns the number of seconds shown in years and days
 *
 * @param int $lastaccess
 * @param array $str ?
 * @return string
 */
function format_time_uc3m($lastaccess, $str=NULL) {

        $lastaccess = date("d-m-Y", $lastaccess);
        $now = date("d-m-Y");

        if (!isset($str)) {
                $str = new stdClass(); 
                $str->days  = get_string('days');
                $str->year  = get_string('year');
                $str->years = get_string('years');
        }

        $lastaccess = strtotime($lastaccess);
        $now = strtotime($now);

        $remainder = $now - $lastaccess;

        $years     = floor($remainder/YEARSECS);
        $remainder = $remainder - ($years*YEARSECS);
        $days      = floor($remainder/DAYSECS);

        $sy = ($years == 1)  ? $str->year  : $str->years;

        $oyears = '';
        $odays = '';

        if ($years>0)  $oyears  = $years .' '. $sy;
        if ($days>0)  $odays  = $days .' '. $str->days;

        if ($years>0) return trim($oyears .' '. $odays);
        if ($days == 1) return get_string('ayer','core_uc3m');
        if ($days > 1)  return $odays;
        return get_string('today','core_uc3m');
}

/*
 * Devuelve nombre largo del curso con el codigo delante, si no lo tiene y procede
 *
 */

function nombrecurso_concodigo($course_uc3m) {

    if ($course_uc3m->tipo == 'oficial' || $course_uc3m->tipo == 'tcs') {
        return $course_uc3m->cod. ' ' .$course_uc3m->fullname; // Ya tenia el codigo en el nombre largo
    } else {
        return $course_uc3m->fullname;
    }
}


/* CODIGO ANTERIOR PARA MOODLE 1.9 */

/*  Este archivo est¡ codificado en UNIX / UTF -8  (sin BOM!)
 *  Se requiere para la conversion a mayusculas
 */
///$CFG->prefixtraspasos  = 'traspasos.'; 

define("LATIN1_UC_CHARS", "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝ");
define("LATIN1_LC_CHARS", "àáâãäåæçèéêëìíîïðñòóôõöøùúûüý");
    
 function mayusculas($str) {
    $str = strtoupper(strtr($str, LATIN1_LC_CHARS, LATIN1_UC_CHARS));
    return strtr($str, array("ß" => "SS"));
}

/*
 * Genera SQL para excluir a los usuarios de soporte de las consultas
 * excepto si lo pide un usuario de soporte
 * $context
 * $etiquetaid  nombre del campo por el que se va a excluir
 * Devuelve  trozo de consulta SQL   id NOT IN (...)  o nada si no hay usuarios excluidos
 */
function get_sqlexcluirsoporte ($context,$etiquetaid="") {
        $excepciones = get_excepcionessoporte ($context);
        if ($excepciones) 
                $sql = ($etiquetaid?" $etiquetaid":"")." NOT IN ("
                .$excepciones.") ";
        else
                $sql="";                
        return $sql;
}

/*
 * Devuelve lista de ids de usuario de soporte para ese contexto: Ej.:  2,1340,48
 * O "" si no hay
 */
function get_excepcionessoporte ($context) {
        $n = 0;
        $excepciones=""; 
        if (!has_capability("moodle/course:soporte",$context) &&
                  $usuariosexcluidos = get_users_by_capability($context, "moodle/course:soporte", 
                  "u.id", "", "", "", "", "", true, false, false)) {    
                        
                /* Ãºltimos flags: true, false, false
                 *  1Âº true indica incluir al administrador
                 *  2Âº false se incluyen tanto los usuarios ocultos como los visibles (tiene que ser false, pues de lo contrario
                 * no se incluirian los roles de soporte pues tambien estan ocultos)
                 *  3Âº no se mira este flag porque no se indica ningun grupo (esta groups a "")
                 */
                                
                foreach($usuariosexcluidos as $u) {
                        if ($n>0) $excepciones.=",";
                $excepciones.=(string)$u->id;
                $n++;                         
                }
        }
        return $excepciones;
}


/*
 * Devuelve nombre largo del curso con el grupo al final para portada de los cursos y emails
 * 
 */
 
function nombrecurso_ampliado($nombrecorto,$nombrelargo) {
        $textogrupo='';
    $patron="/^[C]{1}[0-9]+\.[0-9]+\.[0-9]+.*$/"; # codigo asignatura no tcs ni magistral
        if (preg_match($patron,$nombrecorto)) {
                $partescodigo = explode("-", $nombrecorto);
                $textogrupo=$partescodigo[1];           
        }
        if ($textogrupo) $textogrupo = ". ".get_string('grupo','uc3m',$textogrupo);
        return $nombrelargo.$textogrupo;
}



/**
 * Omar: Replaces strange chars in a string
 * 
 * @param $str string where we want to replace the chars
 * @return the string without strange chars
 */
function replace_chars($str){
        $table = array(
        'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y',
        'þ'=>'b', 'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', 'ª'=>'.'
    );
        return(strtr($str,$table));
}

function cortar_cadena ($cadena,$max_caracteres) {
        $cadena=(string)$cadena;           # variable de otro tipo
        if (empty($cadena)) return ''; # variable not set       
        if (strlen($cadena)<=$max_caracteres) 
                return $cadena; # cadena era correcta
        else
                return substr($cadena,0,$max_caracteres); # cadena cortada      
}

function compactar_cadena ($cadena,$longitud_maxima=null) {

    $longitud_cadena = strlen ($cadena);
        if (!empty($longitud_maxima) && $longitud_cadena>$longitud_maxima) {
                
                $separador = '...';
        $longitud_separador = strlen($separador)+2; // sumar 2 blancos
        
        $palabras = explode(' ',$cadena);
        $total_palabras = count ($palabras);
        $longitud = strlen ($palabras[0]); // minimo primera palabra
        $indice_inicial = 0;
        $indice_final = $total_palabras+1;
        $siguiente = 'principio';
        while ($indice_final>$indice_inicial && $longitud+$longitud_separador   <=$longitud_maxima) {
                if ($siguiente == 'principio') {
                        $indice_final--;
                        $longitud += strlen ($palabras[$indice_inicial+1]);
                        $siguiente = 'final';
                } else {
                        $indice_inicial++;                      
                        $longitud += strlen ($palabras[$indice_final-1]);
                        $siguiente = 'principio';
                }
        }
        if ($indice_inicial+2>=$indice_final) { 
                return $cadena;
        } else {
                $cadena='';
                for ($i=0; $i<=$indice_inicial; $i++) $cadena=$cadena.$palabras[$i].' ';        
                $cadena .=$separador.' ';
                for ($i=$indice_final; $i<=$total_palabras-1; $i++) $cadena=$cadena.$palabras[$i].' ';
                $cadena = substr ($cadena,0,-1); // quitar ultimo espacio
                return $cadena;
        }

        }
        
        return $cadena;
}


function miscursos_comparar ($a, $b) {
        
        // Orden 0. Por vigencia  (0=no vigente, 1=vigente, 2=curso atemporal->vigente siempre)
        if (empty($a->vigente)) $a->vigente2='no'; else $a->vigente2='si';
        if (empty($b->vigente)) $b->vigente2='no'; else $b->vigente2='si';
        $vigente_orden = array ('si'=>1,'no'=>2);
        
        $orden0 = strcmp($vigente_orden[$a->vigente2], $vigente_orden[$b->vigente2]); # primero los vigentes    
        if ($orden0 != 0) return $orden0;
        
        // Orden 1. Por oficial-->tcs-->otros
        $tipo_orden = array ('oficial'=>1,'tcs'=>2,'otros'=>3);
        if ($a->tipo!='oficial' && $a->tipo!='tcs') $a->tipo2='otros'; else $a->tipo2=$a->tipo;
        if ($b->tipo!='oficial' && $b->tipo!='tcs') $b->tipo2='otros'; else $b->tipo2=$b->tipo;
        
        $orden1 = strcmp($tipo_orden[$a->tipo2], $tipo_orden[$b->tipo2]);
        if ($orden1 != 0) return $orden1;
                
        if ($a->tipo=='oficial' && $b->tipo=='oficial') {
                // Orden 2. Por ano academico solo para los oficiales
                # $orden2 = strcmp($a->ano_academico, $b->ano_academico); # primero anos anteriores
                $orden2 = strcmp($b->ano_academico, $a->ano_academico); # primero anos nuevos
                if ($orden2 != 0) return $orden2;
                
                // Orden 3. Por cuatrimestre solo para los oficiales
                $orden3 = strcmp($a->valor_periodo, $b->valor_periodo);
                if ($orden3 != 0) return $orden3;               
        }
        
        // Orden 4. Por el texto que se va a mostrar
        $orden4 = strnatcasecmp($a->mostrar, $b->mostrar);
        
        return $orden4;
                
} // function miscursos_comparar

function miscursos_ordenar (&$cursos) {
        
    uasort($cursos, 'miscursos_comparar');
} // function miscursos_ordenar

function curso_cargar_uc3m (&$curso) {
        global $DB;
        if (empty($curso->id)) return;
        $campos = array('cod','tipo','nombre','nombre_en','cod_grupo','cat_agrupacion');
        $campos_select = implode(',',$campos);
        if ($curso_uc3m = $DB->get_record('course_uc3m',array('id'=>$curso->id),$campos_select)) {
                foreach ($campos as $campo) {
                        $curso->$campo = $curso_uc3m->$campo;
                }               
        }       
        return;  
}

function curso_mostrar_uc3m (&$curso) {

    if (empty($curso->tipo)) $curso->tipo = 'otros';
    if (empty($curso->nombre)) $curso->nombre = $curso->fullname;

        $idioma = (substr(current_language(),0,2)=='en')?'en':'es'; // idioma 'en' para ingles, 'es' para el resto
        $nombre_idioma = ($idioma=='en' && !empty($curso->nombre_en)?$curso->nombre_en:$curso->nombre);
        
        $curso->mostrar = compactar_cadena($nombre_idioma,35);
        # el grupo 1 se oculta. Saldra (nada), -2, -3, -M, -2M, -3M...
        if ($curso->tipo=='oficial' && ($curso->cod_grupo>1 || $curso->cat_agrupacion=='M') ) {  
                $curso->mostrar .= '-';
                if ($curso->cod_grupo>1) $curso->mostrar .= $curso->cod_grupo;
                if (!empty($curso->cat_agrupacion)) {
                        if ($curso->cat_agrupacion=='M') $curso->mostrar .= 'M'; // agrupacion magistral
                        else $curso->mostrar .= '*'; // otra agrupacion distinta
                }               
        } /* else if ($curso->tipo=='tcs' && !empty($curso->edicion)) {
                $curso->mostrar .= '-E'.$curso->edicion;
        } */

        if ($curso->tipo=='oficial' || $curso->tipo=='tcs') {
                $curso->mostrar_detalle = $curso->cod.' '.$curso->fullname;
        } else { 
                $curso->mostrar_detalle = $curso->fullname;
        }
                        
}

function miscursos_generar_mostrar(&$cursos) {
        global $COURSE;
        
        $duplicados = array();
        foreach ($cursos as $clave=>&$curso) {
                        
                ## Quitar de "Mis cursos" si el curso es no vigentes salvo que sea el activo 
                if (empty($curso->vigente) && $curso->id!=$COURSE->id) {
                        unset ($cursos[$clave]);
                        continue;
                }

                ## Generar campos ->mostrar y ->mostrar_detalle
                curso_mostrar_uc3m($curso);
                
                // Si se almacenan varios id para el mismo tipo, ano_academico y texto, 
                // se trata de un item duplicado, que se procesara luego para poner distintivos a los textos
                
                if (!empty($curso->mostrar) && ($curso->tipo=='oficial' || $curso->tipo=='tcs')) {
                        $duplicados[$curso->mostrar][]=$curso->id;                      
                }
                
        } // foreach
        
        // Buscar si hay duplicados e incluir texto que los diferencie (solo para oficial y tcs)        
        foreach ($duplicados as $duplicados_mostrar) {                          
                if (count($duplicados_mostrar)>1) {
                        $centros=array();
                        $estudios=array();
                        $ediciones=array();
                        $ano_academicos=array();
                        foreach ($duplicados_mostrar as $id) {
                                $centros[$cursos[$id]->cod_centro]=1;
                                $estudios[$cursos[$id]->cod_estudio]=1;
                                $estudios[$cursos[$id]->cod_ep]=1;
                                $ediciones[$cursos[$id]->edicion]=1;            
                                $ano_academicos[$cursos[$id]->ano_academico]=1;                         
                        }
                        unset ($estudios[0]); // en el caso de cursos oficiales, cod_ep vale 0                                  
                        foreach ($duplicados_mostrar as $id) {
                                if (count($ediciones)>1) { // ediciones distintas (TCS)
                                        $cursos[$id]->mostrar .= "-E".$cursos[$id]->edicion;    
                                } else if (count($ano_academicos)>1) { // anos academicos distintos
                                        $cursos[$id]->mostrar .= " (".get_cursoacademico($cursos[$id]->ano_academico,'xxxx/xx').")";    
                                } else if (count($centros)>1) { // centros distintos
                                        $cursos[$id]->mostrar .= " (C".$cursos[$id]->cod_centro.")";    
                                } else if (count($estudios)>1) { // estudios distintos
                                        $cursos[$id]->mostrar .= " (C".$cursos[$id]->cod_centro.".".$cursos[$id]->cod_estudio.")";                                                                                      
                                } else { // asignatura distinta, ponemos el codigo completo
                                        $cursos[$id]->mostrar .= " (".$cursos[$id]->cod.")";
                                }
                        }
                } // if
        }
}

/**
 * A partir de un ano xxxx genera una representacion del ano academico mas legible. DRG cambio al formato xxxx-xx
 * @param $ano_academico
 * @param string $formato Formatos validos 'xx-xx'(predeterminado), 'xxxx-xx'
 */
function get_cursoacademico ($ano_academico,$formato='xxxx-xx') {
        $ano_academico=(int)$ano_academico;
        if ($ano_academico>1000 && $ano_academico<3000) {
                switch ($formato) {
                        case 'xxxx-xx':
                                $devolver=$ano_academico.'-'.substr($ano_academico+1,-2,2);
                                break;
                        case 'xx-xx':
                        default:
                                $devolver=substr($ano_academico,-2,2).'-'.substr($ano_academico+1,-2,2);                                
                }
        } else {
                $devolver='';
        }
        return $devolver;
}

function process_file_photo ($file, $userfield, $overwrite) {
    global $DB, $OUTPUT;
    $path_parts = pathinfo(cleardoubleslashes($file));
    $basename  = $path_parts['basename'];
    $extension = $path_parts['extension'];

    $uservalue = substr($basename, 0, strlen($basename) - strlen($extension) - 1);
    if (!($user = $DB->get_record('user', array ($userfield => $uservalue, 'deleted' => 0)))) {
        $a = new stdClass();
        $a->userfield = clean_param($userfield, PARAM_CLEANHTML);
        $a->uservalue = clean_param($uservalue, PARAM_CLEANHTML);
        //echo $OUTPUT->notification(get_string('uploadpicture_usernotfound', 'tool_uploaduser', $a));
        return PIX_FILE_ERROR;
    }
    $haspicture = $DB->get_field('user', 'picture', array('id'=>$user->id));
    if ($haspicture && !$overwrite) {
        //echo $OUTPUT->notification(get_string('uploadpicture_userskipped', 'tool_uploaduser', $user->username));
        return PIX_FILE_SKIPPED;
    }
    $context = context_user::instance($user->id);
    
    $newrev =  process_new_icon($context, 'user', 'icon', 0, $file);
    
    if ($newrev) {
        $DB->set_field('user', 'picture', $newrev, array('id'=>$user->id));
        //echo $OUTPUT->notification(get_string('uploadpicture_userupdated', 'tool_uploaduser', $user->username), 'notifysuccess');
        return PIX_FILE_UPDATED;
    } else {
        //echo $OUTPUT->notification(get_string('uploadpicture_cannotsave', 'tool_uploaduser', $user->username));
        return PIX_FILE_ERROR;
    }
}

function uvselector_inicial ($userid, $username, $correo) {
  global $DB, $CFG;
  $numpar = 2;
  $html = '';
  $ROWS1 = ''; 
  $ROWS2 = ''; 
  $html = '';

  $sql = "SELECT trim(corr_identificacion) FROM mdl_uv_correo_ki WHERE corr_direccion = '$correo' and char_length(corr_identificacion) = '5'";
  $result = $DB->get_field_sql($sql);

  if (strlen($result) == 5) {
    $sql = "SELECT DISTINCT 'c' || substring(to_char(O.CURSO_ACAD,'9999') from 4 for 4) || lower(O.TLUGAR1) || to_char(O.LUGAR1,'FM000') || 'a' || MO.MODULO || 'g' || P.GRUPO AS clave,
           O.CURSO_ACAD -1||'-'||substring(to_char(O.CURSO_ACAD,'9999') from 4 for 4) ||' ' ||MO.VNOMBRE || ' Gr.'||P.GRUPO || ' (' || MO.MODULO || ')' AS nombre,
           O.CURSO_ACAD -1||'-'||substring(to_char(O.CURSO_ACAD,'9999') from 4 for 4) ||' ' ||MO.VNOMBRE || ' Gr.'||P.GRUPO || ' (' || MO.MODULO || ')' AS nombre_en
      FROM MDL_UV_OCASPROF P, MDL_UV_OCAMOD O, MDL_UV_MODULOS MO
     WHERE P.NUMPAR='2'
       and MO.MODULO=O.MODULO
       and O.NUMPAR='2' and O.NUMPAR=P.NUMPAR and O.MODULO=P.MODULO
       and P.NPI=(SELECT trim(corr_identificacion) FROM mdl_uv_correo_ki WHERE corr_direccion = '$correo' and char_length(corr_identificacion) = '5')";
  } else if (strlen($result) == 7) {
    $sql = ''; 
  }

  $my_asignaturas = $DB->get_recordset_sql($sql);
  $z = 0;
  $asig_all = array();
  $nombres_asig_all = array();
  foreach ($my_asignaturas as $record) {
    $asignatura  = $record->clave;
    $nombre      = $record->nombre;
    array_push($asig_all, $asignatura);
    array_push($nombres_asig_all, $nombre);
    $z++;
  }
  $my_asignaturas = array();
  $my_asignaturas = array_merge($my_asignaturas, $asig_all);
  //DRG
  $html = "<H4>". get_string('reminder', 'local_uv') . "<BR></H4>";
  $html .= "<HR>";
  $html .= "" . get_string('reminderBody','local_uv') . "<BR><BR>";
  $html .= "<A HREF=/selector/admin.php?id=" . $userid . ">" . get_string('Manage_Signatures', 'local_uv') . "</A>";
  $html .= "<BR><BR>";
  $html .= "<H4>" . get_string('activated_Subjects','local_uv') . "</H4>";
  $html .= "<HR>";

  foreach ($my_asignaturas as $communitykey) {
    $sql = "SELECT usuario, dominio, communitykey, idplataforma, fechacreacion, fechamodifica FROM  mdl_uv_configura_portal WHERE communitykey = '$communitykey'";
    $result = $DB->get_recordset_sql($sql);
    if ($result->valid()) {
        foreach ($result as $record) {
          $usuario       = $record->usuario;
          $dominio       = $record->dominio;
          $communitykey  = $record->communitykey;
          $idplataforma  = $record->idplataforma;
          $fechacreacion = $record->fechacreacion;
          $fechamodifica = $record->fechamodifica;
          $course = $DB->get_record('course', array('idnumber'=>"$communitykey"));
          $id = $course->id; 
          $fullname = $course->fullname;
          if ($idplataforma == '1') {
              $dotlrn_url = array();
              $dotlrn_url = parsea_currutaca($communitykey);
              //asignatura activada en
              $link = '<a href=\'https://dotlrn.uv.es/dotlrn/classes/'.$dotlrn_url['centro'].'/'.$dotlrn_url['asignatura'].'/'.$communitykey.'/one-community\'>' . get_string ('iradotlrn', 'local_uv') . '</a>';
          } else if ($idplataforma == '2'){
              $courseurl = new moodle_url('/course/view.php', array('id' => $id));
              $link = "<a href=$courseurl>" . get_string ('iramoodle', 'local_uv') . "</a>";
          }
          $ROWS1 .= "<b>$fullname</b> | $link<br>";
        }
    } else {
          $link = get_string ('noconfigured', 'local_uv');
          $course = $DB->get_record('course', array('idnumber'=>"$communitykey"));
          $id = $course->id;
          $fullname = $course->fullname;
          $ROWS2 .= "<b>$fullname</b> | $link<br>";
    }
  }

  if (($ROWS1 <> '') and ($ROWS2 <> '')) {$ROWS1 .= "<HR class='separadorsubjects'>";} 
  else {
    $ROWS1 .= "" .get_string('Empty_subjects','local_uv') . " " .get_string('if_want_see_moodle_courses','local_uv') . "";
  }
  $html .=  "<FONT SIZE=-1>" . $ROWS1 . $ROWS2 . "</FONT>";
  return $html;
}

//AGUSTIN
//user_from y user_to son objetos
//users_cc y users_bcc es un string con email separados por comas
function email_to_users_UV ($user_from, $user_to, $users_cc, $users_bcc, $subject, $messagetext, $messagehtml = '', $attachment = '', $attachname = '',
                             $usetrueaddress = true, $replyto = '', $replytoname = '', $wordwrapwidth = 79) {
    global $CFG;
    $mail = get_mailer();
    $temprecipients = array();
    $tempreplyto = array();
    $supportuser = core_user::get_support_user();
        
    if (!empty($CFG->handlebounces)) {
        $modargs = 'B'.base64_encode(pack('V', $user_from->id)).substr(md5($user_from->email), 0, 16);
        $mail->Sender = generate_email_processing_address(0, $modargs);
    } else {
        $mail->Sender = $supportuser->email;
    }

    if (!empty($CFG->emailonlyfromnoreplyaddress)) {
        $usetrueaddress = false;
        if (empty($replyto) && $user_from->maildisplay) {
            $replyto     = $user_from->email;
            $replytoname = fullname($user_from);
        }
    }
    
    $mail->From     = $user_from->email;
    if (!empty($user_from->alternateid)) {
      $mail->FromName = "";
    } else {        
      $mail->FromName = fullname($user_from);
    }
    syslog (LOG_NOTICE, "From=" . $mail->From);

    /*
    if (is_string($user_from)) {
        $mail->From     = $CFG->noreplyaddress;
        $mail->FromName = $user_from;
    } else if ($usetrueaddress and $user_from->maildisplay) {
        $mail->From     = $user_from->email;
        $mail->FromName = fullname($user_from);
    } else {
        $mail->From     = $CFG->noreplyaddress;
        $mail->FromName = fullname($user_from);
        if (empty($replyto)) {
          $tempreplyto[] = array($CFG->noreplyaddress, get_string('noreplyname'));
        }
    }
    */

    if (!empty($replyto)) {
      $tempreplyto[] = array($replyto, $replytoname);
    }

    $mail->Subject = substr($subject, 0, 900);
    $temprecipients[] = array($user_from->email, fullname($user_from));
    $mail->WordWrap = $wordwrapwidth;

    if (!empty($user_from->customheaders)) {
        if (is_array($user_from->customheaders)) {
            foreach ($user_from->customheaders as $customheader) {
                $mail->addCustomHeader($customheader);
            }
        } else {
            $mail->addCustomHeader($user_from->customheaders);
        }
    }

    if (!empty($user_from->priority)) {$mail->Priority = $user_from->priority;}

    $mail->isHTML(true);
    $mail->Encoding = 'quoted-printable';
    $mail->Body     =  $messagehtml;
    $mail->AltBody  =  "\n$messagetext\n";

    if ($attachment && $attachname) {
        if (preg_match( "~\\.\\.~" , $attachment )) {
            // Security check for ".." in dir path.
            $temprecipients[] = array($supportuser->email, fullname($supportuser, true));
            $mail->addStringAttachment('Error in attachment.  User attempted to attach a filename with a unsafe name.', 'error.txt', '8bit', 'text/plain');
        } else {
            require_once($CFG->libdir.'/filelib.php');
            $mimetype = mimeinfo('type', $attachname);

            $attachmentpath = $attachment;

            // Before doing the comparison, make sure that the paths are correct (Windows uses slashes in the other direction).
            $attachpath = str_replace('\\', '/', $attachmentpath);
            // Make sure both variables are normalised before comparing.
            $temppath = str_replace('\\', '/', $CFG->tempdir);

            // If the attachment is a full path to a file in the tempdir, use it as is,
            // otherwise assume it is a relative path from the dataroot (for backwards compatibility reasons).
            if (strpos($attachpath, $temppath) !== 0) {
                $attachmentpath = $CFG->dataroot . '/' . $attachmentpath;
            }

            $mail->addAttachment($attachmentpath, $attachname, 'base64', $mimetype);
        }
    }

    if ((!empty($CFG->sitemailcharset) || !empty($CFG->allowusermailcharset))) {
        $charset = $CFG->sitemailcharset;
        if (!empty($CFG->allowusermailcharset)) {
            if ($useremailcharset = get_user_preferences('mailcharset', '0', $user->id)) {
                $charset = $useremailcharset;
            }
        }
        $charsets = get_list_of_charsets();
        unset($charsets['UTF-8']);
        if (in_array($charset, $charsets)) {
            $mail->CharSet  = $charset;
            $mail->FromName = core_text::convert($mail->FromName, 'utf-8', strtolower($charset));
            $mail->Subject  = core_text::convert($mail->Subject, 'utf-8', strtolower($charset));
            $mail->Body     = core_text::convert($mail->Body, 'utf-8', strtolower($charset));
            $mail->AltBody  = core_text::convert($mail->AltBody, 'utf-8', strtolower($charset));

            foreach ($temprecipients as $key => $values) {
                $temprecipients[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
            }            
            foreach ($tempreplyto as $key => $values) {
                $tempreplyto[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
            }
                      
        }
    }

    foreach ($temprecipients as $values) {
        $mail->addAddress($values[0], $values[1]);
        syslog (LOG_NOTICE, "addAddress=" . $values[0] . "," . $values[1]);
    }
    foreach ($tempreplyto as $values) {
        $mail->addReplyTo($values[0], $values[1]);
        syslog (LOG_NOTICE, "addReplyTo=" . $values[0] . "," . $values[1]);
    }
    syslog (LOG_NOTICE, "users_cc=" . $users_cc);
    foreach (explode(',', $users_cc) as $user_cc) {
        $mail->addCC($user_cc, '');    
        syslog (LOG_NOTICE, "user_cc=" . $user_cc);  
    }
    syslog (LOG_NOTICE, "users_bcc=" . $users_bcc);
    foreach (explode(',', $users_bcc) as $user_bcc) {
        $mail->addBCC($user_bcc, '');      
        syslog (LOG_NOTICE, "user_bcc=" . $user_bcc);
    }
    
    if ($mail->send()) {
        set_send_count($user_from);
        return true;
    } else {
        // Trigger event for failing to send email.
        $event = \core\event\email_failed::create(array(
            'context' => context_system::instance(),
            'userid' => $user_from->id,
            'relateduserid' => $user_from->id,
            'other' => array(
                'subject' => $subject,
                'message' => $messagetext,
                'errorinfo' => $mail->ErrorInfo
            )
        ));
        $event->trigger();
        if (CLI_SCRIPT) {
            mtrace('Error: lib/moodlelib.php email_to_user(): '.$mail->ErrorInfo);
        }
        return false;
    }
}
//DRG activa-desactiva multilang asignaturas
function get_core_lang_uv () {
  global $DB;
  $core_lang_uv=0;
  #desactiva multilang para asignaturas
  #update mdl_uv_config set valor='0' where nombre='core_lang_uv';
  $core_lang_uv = $DB->get_field('uv_config','valor', array('nombre'=>core_lang_uv));
  return $core_lang_uv;
}
 
?>
