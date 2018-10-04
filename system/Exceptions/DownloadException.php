<?php namespace CodeIgniter\Exceptions;

/**
 * Class DownloadException
 *
 * @package CodeIgniter\Exceptions
 */
class DownloadException extends \RuntimeException implements ExceptionInterface
{

	public static function forCannotSetFilePath(string $path)
	{
		return new static(lang('HTTP.cannotSetFilePath', [$path]));
	}

	public static function forCannotSetBinary()
	{
		return new static(lang('HTTP.cannotSetBinary'));
	}

	public static function forNotFoundDownloadSource()
	{
		return new static(lang('HTTP.notFoundDownloadSource'));
	}

}
