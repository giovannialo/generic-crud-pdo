<?php

namespace Crud;

use PDO;
use PDOStatement;
use PDOException;

/**
 * Class Create <p>
 * Classe responsável por cadastros genéricos no banco de dados.
 * </p>
 */
class Create {

	/** @var string */
	private $table;

	/** @var array */
	private $data;

	/** @var int */
	private $result;

	/** @var PDOStatement */
	private $create;

	/** @var PDO */
	private $conn;

	/**
	 * Create constructor. <p>
	 * Obtém conexão do banco de dados Singleton Pattern.
	 * </p>
	 */
	public function __construct() {
		$this->conn = Conn::getConn();
	}

	/**
	 * getResult. <p>
	 * Retorna o ID do registro inserido ou NULL caso nenhum registro seja inserido.
	 * </p>
	 *
	 * @return int ID do registro inserido
	 */
	public function getResult() {
		return $this->result;
	}

	/**
	 * exeCreate. <p>
	 * Executa um cadastro simplificado no banco de dados utilizando o
	 * preparedStatement. Basta informar o nome da tabela e um array atribuitivo
	 * com o nome da coluna e valor.
	 * </p>
	 *
	 * @param string $table Nome da tabela
	 * @param array  $data Array atribuitivo (nomeDaColuna => valor)
	 */
	public function exeCreate($table, array $data) {
		$this->table = (string) $table;
		$this->data = $data;
		$this->syntax();
		$this->execute();
	}

	/**
	 * exeCreateMultiple. <p>
	 * Executa um cadastro multiplo no banco de dados utilizando o preparedStatement.
	 * Basta informar o nome da tabela e um array multidimensional com o nome da
	 * coluna e valores.
	 * </p>
	 *
	 * @param string $table Nome da tabela
	 * @param array  $data Array multidimensional atribuitivo ([] = Key => Value)
	 */
	public function exeCreateMultiple($table, array $data) {
		$this->table = (string) $table;
		$this->data = $data;

		$fileds = implode(', ', array_keys($this->data[0]));
		$links = count(array_keys($this->data[0]));
		$places = null;
		$inserts = null;

		foreach ($data as $valueMultiple):
			$places .= '(';
			$places .= str_repeat('?,', $links);
			$places .= '),';

			foreach ($valueMultiple as $valueSingle):
				$inserts[] = $valueSingle;
			endforeach;
		endforeach;

		$places = str_replace(',)', ')', $places);
		$places = substr($places, 0, -1);

		$this->data = $inserts;
		$this->create = "INSERT INTO {$this->table} ({$fileds}) VALUES {$places}";
		$this->execute();
	}

	/**
	 * connect. <p>
	 * Obtém o PDO e prepara a query.
	 * </p>
	 */
	private function connect() {
		$this->create = $this->conn->prepare($this->create);
	}

	/**
	 * syntax. <p>
	 * Cria a sintaxe da query para preparedStatement.
	 * </p>
	 */
	private function syntax() {
		$fileds = implode(', ', array_keys($this->data));
		$places = ':' . implode(', :', array_keys($this->data));

		$this->create = "INSERT INTO {$this->table} ({$fileds}) VALUES ({$places})";
	}

	/**
	 * execute. <p>
	 * Obtém a conexão, a syntax e executa a query.
	 * </p>
	 */
	private function execute() {
		try {
			$this->connect();
			$this->create->execute($this->data);
			$this->result = $this->conn->lastInsertId();
		} catch (PDOException $e) {
			$this->result = null;
			trigger_error("<b>Erro ao cadastrar:</b> {$e->getMessage()} {$e->getCode()}", E_USER_ERROR);
			die;
		}
	}

}
