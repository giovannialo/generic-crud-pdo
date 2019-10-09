<?php

namespace Crud;

use PDO;
use PDOException;

/**
 * Class Conn <p>
 * Classe abstrata de conexão SingleTon Pattern.
 * Retorna um objeto PDO pelo método estático getConn().
 * </p>
 */
class Conn {

	/** @var PDO */
	private static $connect = null;

	/**
	 * Conn constructor. <p>
	 * Construtor privado previne que uma nova instância da Classe
	 * seja criada através do operador `new` de fora dessa classe.
	 * </p>
	 */
	private function __construct() {
	}

	/**
	 * getConn. <p>
	 * Retorna um objeto PDO Singleton Pattern.
	 * </p>
	 *
	 * @return PDO Objeto da conexão
	 */
	public static function getConn() {
		return self::connect();
	}

	/**
	 * connect. <p>
	 * Conecta com o banco de dados com o singleton pattern.
	 * Retorna um objeto PDO!
	 * </p>
	 *
	 * @return PDO Objeto da conexão
	 */
	private static function connect() {
		try {
			if (self::$connect == null):
				$dsn = 'mysql:host=' . SIS_DB_HOST . ';dbname=' . SIS_DB_DBSA;
				$options = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'];

				self::$connect = new PDO($dsn, SIS_DB_USER, SIS_DB_PASS, $options);
				self::$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			endif;
		} catch (PDOException $e) {
			trigger_error("Erro ao tentar se conectar com o banco de dados: {$e->getMessage()} {$e->getCode()}", E_USER_ERROR);
			die;
		}

		return self::$connect;
	}

	/**
	 * __clone. <p>
	 * Método clone do tipo privado previne a clonagem dessa instância da classe.
	 * </p>
	 */
	private function __clone() {
	}

	/**
	 * __wakeup. <p>
	 * Método unserialize do tipo privado para previnir a desserialização da
	 * instância dessa classe.
	 * </p>
	 */
	private function __wakeup() {
	}

}
