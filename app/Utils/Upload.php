<?php
declare(strict_types=1);

namespace App\Utils;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use League\Flysystem\Filesystem;

class Upload
{
    /**
     * @var string
     */
    public $name = 'file';

    /**
     * @var string
     */
    public $uploadPath = 'upload';

    /**
     * @var string
     */
    public $extension = '';

    /**
     * @var array
     */
    public $fileInfo = [];

    /**
     * @var string
     */
    public $mineType = '';

    /**
     * @var int
     */
    public $limitWidth = 100;

    /**
     * @var int
     */
    public $limitHeight = 100;

    /**
     * @var bool
     */
    public $resize = true;

    /**
     * @var array
     */
    public $limitType = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'mp4', 'avi', 'rmvb', 'xlsx','xls'];

    /**
     * @var string
     */
    public $limitSize = '';

    /**
     * @Inject()
     * @var Filesystem
     */
    public $fileSystem;

    /**
     * Upload constructor.
     * @param string $name
     * @param string $uploadPath
     * @param int $width
     * @param int $height
     * @param bool $resize
     */
    public function __construct($name='file', $uploadPath='upload', $width=100, $height=100, $size=1024 * 1024 * 10, $resize=false)
    {
        $this->name = $name;
        $this->uploadPath = $uploadPath;
        $this->limitSize = config('service.settings.package_max_length', $size);
        $this->limitWidth = $width;
        $this->limitHeight = $height;
        $this->resize = $resize;
    }

    /**
     * @param RequestInterface $request
     * @param string $dirName
     * @return array
     * @throws \League\Flysystem\FileExistsException
     */
    public function uploadFile(RequestInterface $request, $dirName='')
    {
        if (!$request->hasFile($this->name)) {
            throw new BusinessException(ErrorCode::BAD_REQUEST, '上传文件名称不存在');
        }
        if (!$request->file($this->name)->isValid()) {
            throw new BusinessException(ErrorCode::UNAUTHORIZED, '上传的文件不是有效文件');
        }

        $this->fileInfo = $request->file($this->name)->toArray();
        $this->mineType = $request->file($this->name)->getMimeType();
        $this->extension = $request->file($this->name)->getExtension();

        if ($this->fileInfo['size'] > $this->limitSize) {
            $mSize = $this->limitSize / 1024 / 1024;
            throw new BusinessException(ErrorCode::UNAUTHORIZED, '文件上传大小不能超过' . $mSize . 'MB');
        }
        if (!in_array($this->extension, $this->limitType)) {
            throw new BusinessException(ErrorCode::UNAUTHORIZED, '文件上传格式不支持，支持格式：' . implode(', ', $this->limitType));
        }
        $objectName = env('RESOURCE_TEMP_PATH') ?: BASE_PATH . DIRECTORY_SEPARATOR;

        $filePath = $this->uploadPath . DIRECTORY_SEPARATOR;
        if ($dirName) {
            $filePath = $filePath . $dirName. DIRECTORY_SEPARATOR;
        }
        $filePath = $filePath . date('Y-m-d') . DIRECTORY_SEPARATOR;

        $res = Common::mkDir($objectName . $filePath);
        if (!$res) {
            throw new BusinessException(ErrorCode::UNAUTHORIZED, '资源文件夹创建失败，请检查目录权限');
        }
        $fileName = Common::generateUniqid();
        $fileUrl = $filePath . $fileName . '.' . $this->extension;

        $request->file($this->name)->moveTo($objectName . $fileUrl);

        if (!$request->file($this->name)->isMoved()) {
            throw new BusinessException(ErrorCode::UNAUTHORIZED, '文件上传失败');
        }
        @chmod($objectName . $fileUrl,0777);

        // 上传到oss中
        $stream = fopen($objectName . $fileUrl, 'r+');
        if (!is_resource($stream)) {
            throw new UnauthorizedException('文件上传失败');
        }
        $res = $this->fileSystem->writeStream($fileUrl, $stream);
        if (!$res) {
            throw new UnauthorizedException('文件上传失败');
        }
        if (is_resource($stream)) {
            fclose($stream);
        }

        return [
            'name' => $fileName,
            'fullpath' => env('APP_UPLOAD_HOST_URL', '') . $fileUrl,
            'path' => $fileUrl
        ];
    }


}