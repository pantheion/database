<?php

namespace Pantheion\Database\Table;

use Carbon\Carbon;
use Pantheion\Database\Type\BigInt;
use Pantheion\Database\Type\Bit;
use Pantheion\Database\Type\Char;
use Pantheion\Database\Type\Date;
use Pantheion\Database\Type\DateTime;
use Pantheion\Database\Type\Double;
use Pantheion\Database\Type\FloatColumn;
use Pantheion\Database\Type\Integer;
use Pantheion\Database\Type\Json;
use Pantheion\Database\Type\LongText;
use Pantheion\Database\Type\MediumInt;
use Pantheion\Database\Type\MediumText;
use Pantheion\Database\Type\SmallInt;
use Pantheion\Database\Type\Text;
use Pantheion\Database\Type\Time;
use Pantheion\Database\Type\Timestamp;
use Pantheion\Database\Type\TinyInt;
use Pantheion\Database\Type\TinyText;
use Pantheion\Database\Type\Varchar;

trait ColumnDefinitions
{
    public function add(Column $column)
    {
        $column->table = $this->table;

        return $this->columns[] = $column;
    }

    public function bigInt(string $name)
    {
        return $this->add(new Column($name, BigInt::class));
    }

    public function unsignedBigInt(string $name)
    {
        return $this->add(new Column($name, BigInt::class, ['unsigned' => true]));
    }

    public function bit(string $name, int $length = 1)
    {
        return $this->add(new Column($name, Bit::class, compact('length')));
    }

    public function char(string $name, int $length = 1)
    {
        return $this->add(new Column($name, Char::class, compact('length')));
    }

    public function date(string $name)
    {
        return $this->add(new Column($name, Date::class));
    }

    public function dateTime(string $name)
    {
        return $this->add(new Column($name, DateTime::class));
    }

    public function double(string $name, $length = 16, $precision = 4)
    {
        return $this->add(new Column($name, Double::class, compact('length', 'precision')));
    }

    public function float(string $name, $length = 10, $precision = 2)
    {
        return $this->add(new Column($name, FloatColumn::class, compact('length', 'precision')));
    }

    public function foreign(string $name, string $foreign, string $column = "id")
    {
        return $this->add(
            (new Column($name, BigInt::class, ['unsigned' => true]))->foreign($foreign, $column)
        );
    }

    public function increments(string $name) 
    {
        return $this->add(
            (new Column($name, BigInt::class))->autoIncrement()
        );
    }

    public function integer(string $name)
    {
        return $this->add(new Column($name, Integer::class));
    }

    public function unsignedInt(string $name)
    {
        return $this->add(new Column($name, Integer::class, ['unsigned' => true]));
    }

    public function json(string $name)
    {
        return $this->add(new Column($name, Json::class));
    }

    public function longText(string $name)
    {
        return $this->add(new Column($name, LongText::class));
    }

    public function mediumInt(string $name)
    {
        return $this->add(new Column($name, MediumInt::class));
    }

    public function unsignedMediumInt(string $name)
    {
        return $this->add(new Column($name, MediumInt::class, ['unsigned' => true]));
    }

    public function mediumText(string $name)
    {
        return $this->add(new Column($name, MediumText::class));
    }

    public function primary()
    {
        return $this->add(
            (new Column("id", BigInt::class, ['unsigned' => true]))->primary()->autoIncrement()
        );
    }

    public function smallInt(string $name)
    {
        return $this->add(new Column($name, SmallInt::class));
    }

    public function unsignedSmallInt(string $name)
    {
        return $this->add(new Column($name, SmallInt::class, ['unsigned' => true]));
    }

    public function uuid()
    {
        $this->add(
            (new Column('uuid', Varchar::class, ["length" => 64]))->primary()
        );
    }

    public function text(string $name)
    {
        return $this->add(new Column($name, Text::class));
    }

    public function time(string $name)
    {
        return $this->add(new Column($name, Time::class));
    }

    public function timestamp(string $name)
    {
        return $this->add(new Column($name, Timestamp::class));
    }

    public function timestamps($timezone = 'UTC')
    {
        $this->add(
            (new Column('created_at', Timestamp::class))->nullable()->default(Carbon::now($timezone))
        );

        $this->add(
            (new Column('updated_at', Timestamp::class))->nullable()->default(Carbon::now($timezone))
        );
    }

    public function tinyInt(string $name)
    {
        return $this->add(new Column($name, TinyInt::class));
    }

    public function unsignedTinyInt(string $name)
    {
        return $this->add(new Column($name, TinyInt::class, ['unsigned' => true]));
    }

    public function tinyText(string $name)
    {
        return $this->add(new Column($name, TinyText::class));
    }

    public function varchar(string $name, int $length = 255)
    {
        return $this->add(new Column($name, Varchar::class, compact('length')));
    }
}