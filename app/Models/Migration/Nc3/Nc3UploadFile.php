<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nc3UploadFile extends Model
{
    use HasFactory;

    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'upload_files';

    /**
     * 主キー
     */
    protected $primaryKey = 'id';

    /**
     * タイムスタンプの使用
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'room_id',
        'original_name',
        'real_file_name',
        'path',
        'size',
        'mimetype',
        'extension',
        'plugin_key',
        'content_key',
        'download_count',
        'created',
        'modified',
        'created_user',
        'modified_user',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
        'download_count' => 'integer',
        'created' => 'datetime',
        'modified' => 'datetime',
    ];

    /**
     * ルームとの関連
     */
    public function room()
    {
        return $this->belongsTo(Nc3Room::class, 'room_id');
    }

    /**
     * ファイルのフルパスを取得
     *
     * @return string
     */
    public function getFullPath(): string
    {
        return $this->path . $this->real_file_name;
    }

    /**
     * ファイルが画像かどうかを判定
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mimetype, 'image/');
    }

    /**
     * ファイルサイズを人間が読みやすい形式で取得
     *
     * @return string
     */
    public function getFormattedSize(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * プラグインキーに基づいてファイルをフィルタリングするスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $pluginKey
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForPlugin($query, string $pluginKey)
    {
        return $query->where('plugin_key', $pluginKey);
    }

    /**
     * ルームIDに基づいてファイルをフィルタリングするスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $roomId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForRoom($query, int $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    /**
     * 画像ファイルのみを取得するスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeImages($query)
    {
        return $query->where('mimetype', 'like', 'image/%');
    }

    /**
     * コンテンツキーに基づいてファイルをフィルタリングするスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $contentKey
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForContent($query, string $contentKey)
    {
        return $query->where('content_key', $contentKey);
    }
}
