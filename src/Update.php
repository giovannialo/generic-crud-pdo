<?php

namespace Crud;

use PDO;
use PDOStatement;
use PDOException;

/**
 * Class Update <p>
 * Classe responsável por atualizações genéricas no banco de dados.
 * </p>
 */
class Update {

	/** @var string */
	private $table;

	/** @var array */
	private $data;

	/** @var string */
	private $terms;

	/** @var string */
	private $places;

	/** @var boolean */
	private $result;

	/** @var PDOStatement */
	private $update;

	/** @var PDO */
	private $conn;

	/**
	 * Update constructor. <p>
	 * Classe responsável por atualizações genéricas no banco de dados
	 * </p>
	 */
	public function __construct() {
		$this->conn = Conn::getConn();
	}

	/**
	 * setPlaces. <p>
	 * Método pode ser usado para atualizar o Stored Procedures. Modificando apenas
	 * os valores da condição. Use este método para alterar multiplas linhas.
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
	 * Retorna TRUE se não ocorrer erros, ou FALSE caso contrário. Mesmo não alterando
	 * os dados, se uma query for executada com sucesso, o retorno será TRUE. para
	 * verificar alterações execute o getRowCount().
	 * </p>
	 *
	 * @return bool Se o registro foi ou não atualizado
	 */
	public function getResult() {
		return $this->result;
	}

	/**
	 * getRowCount. <p>
	 * Retorna o número de linhas alteradas no banco.
	 * </p>
	 *
	 * @return int Total de registros alterados
	 */
	public function getRowCount() {
		return $this->update->rowCount();
	}

	/**
	 * exeUpdate. <p>
	 * Executa uma atualização simplificada com preparedStatement. Basta informar
	 * o nome da tabela, os dados a serem atualizados em um array atribuitivo, as
	 * condições e uma análise em cadeia (ParseString) para executar.
	 * </p>
	 *
	 * @param string $table Nome da tabela
	 * @param array  $data Array atribuitivo (nomeDaColuna => Valor)
	 * @param string $terms WHERE coluna = :link AND.. OR..
	 * @param string $parseString link={$link}&link2={$link2}
	 */
	public function exeUpdate($table, array $data, $terms, $parseString) {
		$this->table = (string) $table;
		$this->data = $data;
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
		$this->update = $this->conn->prepare($this->update);
	}

	/**
	 * syntax. <p>
	 * Cria a sintaxe da query para preparedStatement.
	 * </p>
	 */
	private function syntax() {
		$places = [];
		foreach ($this->data as $key => $value):
			$places[] = $key . ' = :' . $key;
		endforeach;
		$places = implode(', ', $places);

		$this->update = "UPDATE {$this->table} SET {$places} {$this->terms}";
	}

	/**
	 * execute. <p>
	 * Obtém a conexão, a syntax e executa a query.
	 * </p>
	 */
	private function execute() {
		try {
			$this->connect();
			$this->setNull();
			$this->update->execute(array_merge($this->data, $this->places));
			$this->result = true;
		} catch (PDOException $e) {
			$this->result = null;
			trigger_error("<b>Erro ao Ler:</b> {$e->getMessage()} {$e->getCode()}", E_USER_ERROR);
			die;
		}
	}

	/**
	 * setNull. <p>
	 * Define os dados vazios ("") para null
	 * </p>
	 */
	private function setNull() {
		foreach ($this->data as $key => $value):
			$this->data[$key] = ($value == "" ? null : $value);
		endforeach;
	}

}
