<?php

namespace Crud;

use PDO;
use PDOStatement;
use PDOException;

/**
 * Class Delete <p>
 * Classe responsável por deletar genéricamente no banco de dados.
 * </p>
 */
class Delete {

	/** @var string */
	private $table;

	/** @var string */
	private $terms;

	/** @var string */
	private $places;

	/** @var boolean */
	private $result;

	/** @var PDOStatement */
	private $delete;

	/** @var PDO */
	private $conn;

	/**
	 * Delete constructor. <p>
	 * Obtém conexão do banco de dados Singleton Pattern.
	 * </p>
	 */
	public function __construct() {
		$this->conn = Conn::getConn();
	}

	/**
	 * setPlaces. <p>
	 * Método pode ser usado para deletar com Stored Procedures modificando apenas
	 * os valores da condição. Use este método para deletar múltiplas linhas.
	 * </p>
	 *
	 * @param string $parseString id={$id}&..
	 */
	public function setPlaces($parseString) {
		parse_str($parseString, $this->places);
		$this->syntax();
		$this->execute();
	}

	/**
	 * getResult. <p>
	 * Retorna TRUE se não ocorrer erros, ou FALSE caso contrário.
	 * </p>
	 *
	 * @return bool Se o registro foi ou não deletado
	 */
	public function getResult() {
		return $this->result;
	}

	/**
	 * getRowCount. <p>
	 * Retorna o número de linhas removidas no banco.
	 * </p>
	 *
	 * @return int Total de registros deletados
	 */
	public function getRowCount() {
		return $this->delete->rowCount();
	}

	/**
	 * exeDelete. <p>
	 * Executa uma remoção simplificada no banco de dados utilizando o preparedStatement.
	 * Basta informar o nome da tabela, os termos da exclusão e uma analize em cadeia
	 * (ParseString) para executar.
	 * </p>
	 *
	 * @param string $table Nome da tabela
	 * @param string $terms WHERE
	 * @param string $parseString id={$id}&..
	 */
	public function exeDelete($table, $terms, $parseString) {
		$this->table = (string) $table;
		$this->terms = (string) $terms;

		parse_str($parseString, $this->places);

		$this->syntax();
		$this->execute();
	}

	/**
	 * connect. <p>
	 * Obtém o PDO e prepara a query.
	 * </p>
	 */
	private function connect() {
		$this->delete = $this->conn->prepare($this->delete);
	}

	/**
	 * syntax. <p>
	 * Cria a sintaxe da query para preparedStatement.
	 * </p>
	 */
	private function syntax() {
		$this->delete = "DELETE FROM {$this->table} {$this->terms}";
	}

	/**
	 * execute. <p>
	 * Obtém a conexão, a syntax e executa a query.
	 * </p>
	 */
	private function execute() {
		try {
			$this->connect();
			$this->delete->execute($this->places);
			$this->result = true;
		} catch (PDOException $e) {
			$this->result = null;
			trigger_error("<b>Erro ao Deletar:</b> {$e->getMessage()} {$e->getCode()}", E_USER_ERROR);
			die;
		}
	}

}
