<?php

namespace Crud;

use PDO;
use PDOStatement;
use PDOException;

/**
 * Class Read <p>
 * Classe responsável por leituras genéricas no banco de dados.
 * </p>
 */
class Read {

	/** @var string value */
	private $select;

	/** @var string */
	private $places;

	/** @var array[][] */
	private $result;

	/** @var PDOStatement */
	private $read;

	/** @var PDO */
	private $conn;

	/**
	 * Read constructor. <p>
	 * Obtém conexão do banco de dados Singleton Pattern.
	 * </p>
	 */
	public function __construct() {
		$this->conn = Conn::getConn();
	}

	/**
	 * setPlaces. <p>
	 * Método pode ser usado para consultar com Stored Procedures modificando apenas
	 * os valores da condição. Use este método para consultar múltiplas linhas.
	 * </p>
	 *
	 * @param string $parseString id={$id}&..
	 */
	public function setPlaces($parseString) {
		parse_str($parseString, $this->places);
		$this->execute();
	}

	/**
	 * getResult. <p>
	 * Retorna um array multidimensional com todos os resultados obtidos. O envelope
	 * primário é numérico. Para obter um resultado chame o indice getResult()[0].
	 * </p>
	 *
	 * @return array[][] Todos os registros encontrados
	 */
	public function getResult() {
		return $this->result;
	}

	/**
	 * getRowCount. <p>
	 * Retorna o número de registros encontados pelo select.
	 * </p>
	 *
	 * @return int
	 */
	public function getRowCount() {
		return $this->read->rowCount();
	}

	/**
	 * exeRead. <p>
	 * Executa uma leitura simplificada com preparedStatement. Basta informar o nome
	 * da tabela, os termos da seleção e uma análise em cadeia (ParseString) para exeuctar.
	 * </p>
	 *
	 * @param string $table Nome da tabela
	 * @param string $terms WHERE | ORDER | LIMIT :limit | OFFSET :offset
	 * @param string $parseString link={$link}&link2={$link2}
	 */
	public function exeRead($table, $terms = null, $parseString = null) {
		if (!empty($parseString)):
			parse_str($parseString, $this->places);
		endif;

		$this->select = "SELECT * FROM {$table} {$terms}";
		$this->execute();
	}

	/**
	 * linkResult. <p>
	 * Obtém resultados relacionaos de outra tabela por meio de coluna e valor associado.
	 * </p>
	 *
	 * @param string $table Nome da tabela
	 * @param string $column Nome da coluna relacionada e sua leitura atual
	 * @param int    $value Valor relacionado, geralmente o ID que se associa a outra tabela
	 * @param string $fields Nome das colunas que deseja ler separadas por vírgula
	 *
	 * @return array[][]|bool
	 */
	public function linkResult($table, $column, $value, $fields = null) {
		if ($fields):
			$this->fullRead("SELECT {$fields} FROM  {$table} WHERE {$column} = :value", "value={$value}");
		else:
			$this->exeRead($table, "WHERE {$column} = :value", "value={$value}");
		endif;

		if ($this->getResult()):
			return $this->getResult()[0];
		else:
			return false;
		endif;
	}

	/**
	 * fullRead. <p>
	 * Executa uma leitura de dados via query que deve ser montada manualmente para
	 * possibilitar seleção de multiplas tabelas em uma única string.
	 * </p>
	 *
	 * @param string $query Query Select Syntax
	 * @param string $parseString link={$link}&link2={$link2}
	 */
	public function fullRead($query, $parseString = null) {
		$this->select = (string) $query;

		if (!empty($parseString)):
			parse_str($parseString, $this->places);
		endif;

		$this->execute();
	}

	/**
	 * connect. <p>
	 * Obtém o PDO e Prepara a query.
	 * </p>
	 */
	private function connect() {
		$this->read = $this->conn->prepare($this->select);
		$this->read->setFetchMode(PDO::FETCH_ASSOC);
	}

	/**
	 * syntax. <p>
	 * Cria a sintaxe da query para preparedStatement.
	 * </p>
	 */
	private function syntax() {
		if ($this->places):
			foreach ($this->places as $key => $value):
				if ($key == 'limit' || $key == 'offset'):
					$value = (int) $value;
				endif;

				$this->read->bindValue(":{$key}", $value, (is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR));
			endforeach;
		endif;
	}

	/**
	 * execute. <p>
	 * Obtém a conexão, a syntax e executa a query.
	 * </p>
	 */
	private function execute() {
		try {
			$this->connect();
			$this->syntax();
			$this->read->execute();
			$this->result = $this->read->fetchAll();
		} catch (PDOException $e) {
			$this->result = null;
			trigger_error("<b>Erro ao consultar:</b> {$e->getMessage()} {$e->getCode()}", E_USER_ERROR);
			die;
		}
	}

}
