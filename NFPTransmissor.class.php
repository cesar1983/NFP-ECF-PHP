<?php

/**
 * Interface PHP para utilização do Webservice da Nota Fiscal Paulista.
 * Envio, Retificação e Consulta de arquivos ECF (Cupom Fiscal)
 * Class NFPTransmissor
 * Author: César Fernandes de Almeida
 * Date: 22/10/2014
 */
class NFPTransmissor {


    protected $wsdl_url;
    protected $xmlns;

    protected $usuario;
    protected $senha;
    protected $cnpj;
    protected $categoriaUsuario;

    protected $cliente;

    /**
     * @param $usuario o usuário é um cpf ou cpnj cadastrado na nfp,
     * @param $senha a senha do usuário é aquela que está cadastrada para acesso ao sistema da nota fiscal paulista
     * @param $cnpj identificação do remetente(empresa) do arquivo gerado
     * @param $categoriaUsuario perfil do usuário informado (1 para contribuintes, 2 para contabilistas e 3 para consumidores)
     * @param string $wsdl_url
     * @param string $xmlns
     * @throws NFPWSException
     */
	public function __construct($usuario, $senha, $cnpj, $categoriaUsuario,
    							$wsdl_url='https://www.nfp.fazenda.sp.gov.br/ws/arquivocf.asmx?wsdl',
    							$xmlns='https://www.nfp.sp.gov.br/ws') {

        $this->setWsdlUrl($wsdl_url);
        $this->setXmlns($xmlns);

        $this->setUsuario($usuario);
        $this->setSenha($senha);
        $this->setCnpj($cnpj);
        $this->setCategoriaUsuario($categoriaUsuario);

        try {

            if($this->cliente == null){

                $opts = array(
                    "trace"        => 1,
                    "exceptions"   => 1,
                    "soap_version" => SOAP_1_2,
                    "classmap"     => array( 'timezone'    => 'TimeZone' ),
                    'http'         => array( 'user_agent' => 'PHPSoapClient' )
                );

                $this->cliente = new SoapClient( $this->getWsdlUrl(), $opts );

                $cabecalho = '<Autenticacao Usuario="'.$this->getUsuario().'"
                                    Senha="'.$this->getSenha().'"
                                    CNPJ="'.$this->getCnpj().'"
                                    CategoriaUsuario="'.$this->getCategoriaUsuario().'"
                                    xmlns="'.$this->getXmlns().'" />';

                $autentica_token = new SoapVar($cabecalho, XSD_ANYXML);


                $header   = new SoapHeader($this->xmlns, 'Autenticacao', $autentica_token);

                $this->cliente->__setSOAPHeaders($header);

            }

        } catch (Exception $e) {
            throw new NFPWSException($e->getMessage());
        }
    }


	public function consultar( $protocoloNr ){

        $strConsulta = '<Consultar xmlns="'.$this->getXmlns().'">
                            <Protocolo>'.$protocoloNr.'</Protocolo>
                        </Consultar>';

        $tokenConsulta = new SoapVar($strConsulta, XSD_ANYXML);

        $resConsulta = $this->cliente->__soapCall('Consultar', array('Consultar' => $tokenConsulta));

        $retorno = new stdClass();
        $retorno = NFPUtil::parseRetornoConsulta($resConsulta);

        return $retorno;
    }


    public function enviar($arquivoNome, $arquivoConteudo, $envioNormal, $observacoes){

        $strEnvio = '<Enviar xmlns="'.$this->getXmlns().'">
                        <NomeArquivo>'.$arquivoNome.'</NomeArquivo>
                        <ConteudoArquivo>'.$arquivoConteudo.'</ConteudoArquivo>
                        <EnvioNormal>'.$envioNormal.'</EnvioNormal>
                        <Observacoes>'.$observacoes.'</Observacoes>
                     </Enviar>';

        $tokenEnvio = new SoapVar($strEnvio, XSD_ANYXML);

        $resEnvio = $this->cliente->__soapCall('Enviar', array('Enviar'=> $tokenEnvio));

        $retorno = new stdClass();
        $retorno = NFPUtil::parseRetornoEnvio($resEnvio);

        return $retorno;
    }


    public function retificar($arquivoNome, $arquivoConteudo, $envioNormal, $observacoes){

        $strEnvio = '<Retificar xmlns="'.$this->getXmlns().'">
                        <NomeArquivo>'.$arquivoNome.'</NomeArquivo>
                        <ConteudoArquivo>'.$arquivoConteudo.'</ConteudoArquivo>
                        <EnvioNormal>'.$envioNormal.'</EnvioNormal>
                        <Observacoes>'.$observacoes.'</Observacoes>
                     </<Retificar>';

        $tokenEnvio = new SoapVar($strEnvio, XSD_ANYXML);

        $resEnvio = $this->cliente->__soapCall('Retificar', array('Retificar'=> $tokenEnvio));

        $retorno = new stdClass();
        $retorno = NFPUtil::parseRetornoEnvio($resEnvio);

        return $retorno;

    }

    /**
     * @param mixed $categoriaUsuario
     */
    public function setCategoriaUsuario($categoriaUsuario)
    {
    	$this->categoriaUsuario = $categoriaUsuario;
    }

    /**
     * @return mixed
     */
    public function getCategoriaUsuario()
    {
    	return $this->categoriaUsuario;
    }

    /**
     * @param mixed $cliente
     */
    public function setCliente($cliente)
    {
    	$this->cliente = $cliente;
    }

    /**
     * @return mixed
     */
    public function getCliente()
    {
    	return $this->cliente;
    }

    /**
     * @param mixed $cnpj
     */
    public function setCnpj($cnpj)
    {
    	$this->cnpj = $cnpj;
    }

    /**
     * @return mixed
     */
    public function getCnpj()
    {
    	return $this->cnpj;
    }

    /**
     * @param mixed $senha
     */
    public function setSenha($senha)
    {
    	$this->senha = $senha;
    }

    /**
     * @return mixed
     */
    public function getSenha()
    {
    	return $this->senha;
    }

    /**
     * @param mixed $usuario
     */
    public function setUsuario($usuario)
    {
    	$this->usuario = $usuario;
    }

    /**
     * @return mixed
     */
    public function getUsuario()
    {
    	return $this->usuario;
    }

    /**
     * @param string $wsdl_url
     */
    public function setWsdlUrl($wsdl_url)
    {
    	$this->wsdl_url = $wsdl_url;
    }

    /**
     * @return string
     */
    public function getWsdlUrl()
    {
    	return $this->wsdl_url;
    }

    /**
     * @param string $xmlns
     */
    public function setXmlns($xmlns)
    {
    	$this->xmlns = $xmlns;
    }

    /**
     * @return string
     */
    public function getXmlns()
    {
    	return $this->xmlns;
    }
}


class NFPUtil {

	public static function parseRetornoEnvio( $resEnvio ){

		if(empty($resEnvio)){
			throw new NFPWSException("Nenhum retorno da trasmissão.");
		}
		$strEnvio = $resEnvio->EnviarResult;

		if(!empty($strEnvio)){

			$ln_exp = explode('|', $strEnvio);

			$NFPRetornoEnvio = new NFPRetornoEnvio();

			if(count($ln_exp)==2){

				$erro = new NFPErro();
 				$erro->setCodigo( $ln_exp[0] );
 				$erro->setDescricao( $ln_exp[1] );
 				$NFPRetornoEnvio->setErro( $erro );

			} else {

				$NFPRetornoEnvio->setData( trim($ln_exp[0]) );
				$NFPRetornoEnvio->setNumeroLote( trim($ln_exp[1]) );
				$NFPRetornoEnvio->setSituacaoLoteCodigo( trim($ln_exp[2]) );
				$NFPRetornoEnvio->setSituacaoLoteDescricao( trim($ln_exp[3]) );

				return $NFPRetornoEnvio;
			}
		}
	}


	public static function parseRetornoConsulta( $resConsulta ){


		if(empty($resConsulta)){
			throw new NFPWSException("Nenhum retorno da consulta.");
		} else {

			$strRetorno = $resConsulta->ConsultarResult;

			$ln_arq = explode("\n", $strRetorno);

			if(!empty($ln_arq)){

				$NFPRetornoConsulta = new NFPRetornoConsulta();

				$alertas = [];
				$erros = [];

				foreach($ln_arq AS $ln){

					if('alerta' == strtolower(substr(trim($ln), 0, 6)))
					{
						$alertas[] = trim($ln);
					}
					elseif('erro' == strtolower(substr(trim($ln), 0, 4)))
					{
						$erros[] = trim($ln);
					}
					else {

						$ln_exp = explode('|', $ln);

						if(count($ln_exp)==2){

							$erro = new NFPErro();
							$erro->setCodigo( $ln_exp[0] );
							$erro->setDescricao( $ln_exp[1] );
							$NFPRetornoConsulta->setErro( $erro );

						} else {

							if(!empty($ln_exp) && !empty($ln_exp[0])){

								//echo '<pre>1 '; print_r($ln_exp); //die();
								//die($ln_exp[0]);

								$NFPRetornoConsulta->setProtocolo( (!empty($ln_exp[0])) ? trim($ln_exp[0]) : '' );
								$NFPRetornoConsulta->setStatus( (!empty($ln_exp[1])) ? trim($ln_exp[1]) : ''  );
								$NFPRetornoConsulta->setAlerta( (!empty($ln_exp[2])) ? trim($ln_exp[2]) : ''  );

								$NFPRetornoConsulta->setCnpj( (!empty($ln_exp[4])) ? trim($ln_exp[4]) : ''  );
								$NFPRetornoConsulta->setRazaoSocial( (!empty($ln_exp[5])) ? trim($ln_exp[5]) : ''  );
								$NFPRetornoConsulta->setResponsavel( (!empty($ln_exp[6])) ? trim($ln_exp[6]) : ''  );

								$NFPRetornoConsulta->setTpProcessamento( (!empty($ln_exp[7])) ? trim($ln_exp[7]) : ''  );
								$NFPRetornoConsulta->setNomeArquivo( (!empty($ln_exp[8])) ? trim($ln_exp[8]) : ''  );
								$NFPRetornoConsulta->setTamanhoArquivo( (!empty($ln_exp[9])) ? trim($ln_exp[9]) : ''  );
								$NFPRetornoConsulta->setHashArquivo( (!empty($ln_exp[10])) ? trim($ln_exp[10]) : ''  );
								$NFPRetornoConsulta->setObservacoes( (!empty($ln_exp[11])) ? trim($ln_exp[11]) : ''  );

								$NFPRetornoConsulta->setDtRecebimento( (!empty($ln_exp[12])) ? trim($ln_exp[12]) : ''  );
								$NFPRetornoConsulta->setDtProcessamento( (!empty($ln_exp[13])) ? trim($ln_exp[13]) : ''  );
								$NFPRetornoConsulta->setTempoProcessamento( (!empty($ln_exp[14])) ? trim($ln_exp[14]) : '' );
								$NFPRetornoConsulta->setDtReferenciaArquivo( (!empty($ln_exp[15])) ? trim($ln_exp[15]) : ''  );

								$NFPRetornoConsulta->setCfsProcessados( (!empty($ln_exp[16])) ? trim($ln_exp[16]) : ''  );
								$NFPRetornoConsulta->setVlProcessadoLote( (!empty($ln_exp[17])) ? trim($ln_exp[17]) : ''  );
							}
						}
					}
				}

				if(!empty($alertas))
					$NFPRetornoConsulta->setAlertas( $alertas );

				if(!empty($erros))
					$NFPRetornoConsulta->setErros( $erros );


				return $NFPRetornoConsulta;
			}
		}
	}
}


class NFPRetornoEnvio {

	private $data;
	private $numeroLote;
	private $situacaoLoteCodigo;
	private $situacaoLoteDescricao;
	private $erro;

	/**
	 * @param mixed $data
	 */
	public function setData($data)
	{
		$this->data = $data;
	}

	/**
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param mixed $numeroLote
	 */
	public function setNumeroLote($numeroLote)
	{
		$this->numeroLote = $numeroLote;
	}

	/**
	 * @return mixed
	 */
	public function getNumeroLote()
	{
		return $this->numeroLote;
	}

	/**
	 * @param mixed $situacaoLoteCodigo
	 */
	public function setSituacaoLoteCodigo($situacaoLoteCodigo)
	{
		$this->situacaoLoteCodigo = $situacaoLoteCodigo;
	}

	/**
	 * @return mixed
	 */
	public function getSituacaoLoteCodigo()
	{
		return $this->situacaoLoteCodigo;
	}

	/**
	 * @param mixed $situacaoLoteDescricao
	 */
	public function setSituacaoLoteDescricao($situacaoLoteDescricao)
	{
		$this->situacaoLoteDescricao = $situacaoLoteDescricao;
	}

	/**
	 * @return mixed
	 */
	public function getSituacaoLoteDescricao()
	{
		return $this->situacaoLoteDescricao;
	}


	public function setErro($erro)
	{
		$this->erro = $erro;
	}
	public function getErro()
	{
		return $this->erro;
	}

}


class NFPRetornoConsulta {

	private $erro;
	private $protocolo;
	private $status;
	private $alerta;
	private $cnpj;
	private $razaoSocial;
	private $responsavel;
	private $tpProcessamento;
	private $nomeArquivo;
	private $tamanhoArquivo;
	private $hashArquivo;
	private $observacoes;
	private $dtRecebimento;
	private $dtProcessamento;
	private $tempoProcessamento;
	private $dtReferenciaArquivo;
	private $cfsProcessados;
	private $vlProcessadoLote;

	private $erros = [];
	private $alertas = [];


	/**
	 * @param mixed $alerta
	 */
	public function setAlerta($alerta)
	{
		$this->alerta = $alerta;
	}

	/**
	 * @return mixed
	 */
	public function getAlerta()
	{
		return $this->alerta;
	}

	/**
	 * @param array $alertas
	 */
	public function setAlertas($alertas)
	{
		$this->alertas = $alertas;
	}

	/**
	 * @return array
	 */
	public function getAlertas()
	{
		return $this->alertas;
	}

	/**
	 * @param mixed $cfsProcessados
	 */
	public function setCfsProcessados($cfsProcessados)
	{
		$this->cfsProcessados = $cfsProcessados;
	}

	/**
	 * @return mixed
	 */
	public function getCfsProcessados()
	{
		return $this->cfsProcessados;
	}

	/**
	 * @param mixed $cnpj
	 */
	public function setCnpj($cnpj)
	{
		$this->cnpj = $cnpj;
	}

	/**
	 * @return mixed
	 */
	public function getCnpj()
	{
		return $this->cnpj;
	}

	/**
	 * @param mixed $dtProcessamento
	 */
	public function setDtProcessamento($dtProcessamento)
	{
		$this->dtProcessamento = $dtProcessamento;
	}

	/**
	 * @return mixed
	 */
	public function getDtProcessamento()
	{
		return $this->dtProcessamento;
	}

	/**
	 * @param mixed $dtRecebimento
	 */
	public function setDtRecebimento($dtRecebimento)
	{
		$this->dtRecebimento = $dtRecebimento;
	}

	/**
	 * @return mixed
	 */
	public function getDtRecebimento()
	{
		return $this->dtRecebimento;
	}

	/**
	 * @param mixed $dtReferencia_arquivo
	 */
	public function setDtReferenciaArquivo($dtReferencia_arquivo)
	{
		$this->dtReferenciaArquivo = $dtReferencia_arquivo;
	}

	/**
	 * @return mixed
	 */
	public function getDtReferenciaArquivo()
	{
		return $this->dtReferenciaArquivo;
	}

	/**
	 * @param array $erros
	 */
	public function setErros($erros)
	{
		$this->erros = $erros;
	}

	/**
	 * @return array
	 */
	public function getErros()
	{
		return $this->erros;
	}

	/**
	 * @param mixed $hashArquivo
	 */
	public function setHashArquivo($hashArquivo)
	{
		$this->hashArquivo = $hashArquivo;
	}

	/**
	 * @return mixed
	 */
	public function getHashArquivo()
	{
		return $this->hashArquivo;
	}

	/**
	 * @param mixed $nome
	 */
	public function setNome($nome)
	{
		$this->nome = $nome;
	}

	/**
	 * @return mixed
	 */
	public function getNome()
	{
		return $this->nome;
	}

	/**
	 * @param mixed $nomeArquivo
	 */
	public function setNomeArquivo($nomeArquivo)
	{
		$this->nomeArquivo = $nomeArquivo;
	}

	/**
	 * @return mixed
	 */
	public function getNomeArquivo()
	{
		return $this->nomeArquivo;
	}

	/**
	 * @param mixed $observacoes
	 */
	public function setObservacoes($observacoes)
	{
		$this->observacoes = $observacoes;
	}

	/**
	 * @return mixed
	 */
	public function getObservacoes()
	{
		return $this->observacoes;
	}

	/**
	 * @param mixed $protocolo
	 */
	public function setProtocolo($protocolo)
	{
		$this->protocolo = $protocolo;
	}

	/**
	 * @return mixed
	 */
	public function getProtocolo()
	{
		return $this->protocolo;
	}

	/**
	 * @param mixed $razaoSocial
	 */
	public function setRazaoSocial($razaoSocial)
	{
		$this->razaoSocial = $razaoSocial;
	}

	/**
	 * @return mixed
	 */
	public function getRazaoSocial()
	{
		return $this->razaoSocial;
	}

	/**
	 * @param mixed $responsavel
	 */
	public function setResponsavel($responsavel)
	{
		$this->responsavel = $responsavel;
	}

	/**
	 * @return mixed
	 */
	public function getResponsavel()
	{
		return $this->responsavel;
	}

	/**
	 * @param mixed $status
	 */
	public function setStatus($status)
	{
		$this->status = $status;
	}

	/**
	 * @return mixed
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param mixed $tamanhoArquivo
	 */
	public function setTamanhoArquivo($tamanhoArquivo)
	{
		$this->tamanhoArquivo = $tamanhoArquivo;
	}

	/**
	 * @return mixed
	 */
	public function getTamanhoArquivo()
	{
		return $this->tamanhoArquivo;
	}

	/**
	 * @param mixed $tempoProcessamento
	 */
	public function setTempoProcessamento($tempoProcessamento)
	{
		$this->tempoProcessamento = $tempoProcessamento;
	}

	/**
	 * @return mixed
	 */
	public function getTempoProcessamento()
	{
		return $this->tempoProcessamento;
	}

	/**
	 * @param mixed $tpProcessamento
	 */
	public function setTpProcessamento($tpProcessamento)
	{
		$this->tpProcessamento = $tpProcessamento;
	}

	/**
	 * @return mixed
	 */
	public function getTpProcessamento()
	{
		return $this->tpProcessamento;
	}

	/**
	 * @param mixed $vlProcessadoLote
	 */
	public function setVlProcessadoLote($vlProcessadoLote)
	{
		$this->vlProcessadoLote = $vlProcessadoLote;
	}

	/**
	 * @return mixed
	 */
	public function getVlProcessadoLote()
	{
		return $this->vlProcessadoLote;
	}

	public function setErro($erro)
	{
		$this->erro = $erro;
	}
	public function getErro()
	{
		return $this->erro;
	}
}

class NFPErro {

	private $codigo;
	private $descricao;

	public function getCodigo(){
		return $this->codigo;
	}
	public function setCodigo($codigo)
	{
		$this->codigo = $codigo;
	}

	public function getDescricao(){
		return $this->descricao;
	}
	public function setDescricao($descricao)
	{
		$this->descricao = $descricao;
	}
}


// @codeCoverageIgnoreStart
class NFPWSException extends Exception {
	public function __construct($message = '', $cause = NULL) {
		parent::__construct($message, 0, $cause);
	}
}



