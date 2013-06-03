<?php

namespace RPI\Utilities\ContentBuild\Processors;

class Sprites implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    const VERSION = "1.0.9";
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    protected $project = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Processor 
     */
    protected $processor = null;
    
    const MAX_SPRITE_WIDTH = 1024;
    const SPRITE_PADDING = 2;
    
    protected $maxSpriteWidth = self::MAX_SPRITE_WIDTH;

    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->processor = $processor;
        $this->project = $project;
        
        if (isset($options, $options["maxSpriteWidth"])) {
            $this->maxSpriteWidth = $options["maxSpriteWidth"];
            if (!is_numeric($this->maxSpriteWidth) || $this->maxSpriteWidth < 0 || $this->maxSpriteWidth > 10000) {
                throw new \Exception(__CLASS__.": maxSpriteWidth must be a integer between 0 and 10000");
            }
        }
        
        \RPI\Foundation\Event\Manager::addEventListener(
            "RPI\Utilities\ContentBuild\Events\ImageCheckAvailability",
            function (\RPI\Foundation\Event $event, $params) use ($processor) {
                $sprites = $processor->getMetaData("sprites");
                foreach ($sprites as $sprite) {
                    if ($sprite["spritePath"] == $params["imageUri"]) {
                        $event->srcEvent->setReturnValue(true);
                        break;
                    }
                }
            }
        );
    }
    
    public static function getVersion()
    {
        return "v".self::VERSION;
    }
    
    public function init(
        $processorIndex
    ) {
        $sprites = $this->processor->getMetadata("sprites");
        if (isset($sprites) && $sprites !== false) {
            foreach ($sprites as $sprite) {
                if (file_exists($sprite["spriteName"])) {
                    unlink($sprite["spriteName"]);
                }
                if (file_exists($sprite["spriteDebugName"])) {
                    unlink($sprite["spriteDebugName"]);
                }
            }
        }

        $this->processor->setMetadata("sprites", null);
    }
    
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $buffer
    ) {
        $outputFilename = $build->outputFilename;
        
        $sprites = $this->processor->getMetaData("sprites");
        if ($sprites === false) {
            $sprites = array();
        }
        
        $spritePadding = self::SPRITE_PADDING;
        $maxSpriteWidth = $this->maxSpriteWidth;
        $project = $this->project;

        \RPI\Foundation\Helpers\Utils::pregReplaceCallbackOffset(
            "/(sprite\:\s*url\s*\(\s*'*\"*(.*?)'*\"*\s*\)\s*(.*?);)/sim",
            function ($matches) use (
                $inputFilename,
                $outputFilename,
                $build,
                &$sprites,
                $maxSpriteWidth,
                $resolver,
                $project,
                $spritePadding
                ) {

                $details = $matches[3][0];
                
                $ratio = 1;
                $filenamePostfix = "";
                
                if ($details !== "") {
                    $detailsParts = explode("=", $details);

                    switch (strtolower(trim($detailsParts[0]))) {
                        case "ratio":
                            if (!isset($detailsParts[1])) {
                                throw new \RPI\Foundation\Exceptions\RuntimeException(
                                    "Ratio value must be specifed. e.g. ratio=<x>".
                                    " in '$inputFilename{$matches[3]["fileDetails"]}'"
                                );
                            } elseif (!is_numeric($detailsParts[1])) {
                                throw new \RPI\Foundation\Exceptions\RuntimeException(
                                    "Ratio value '{$detailsParts[1]}' must be an integer".
                                    " in '$inputFilename{$matches[3]["fileDetails"]}'"
                                );
                            }
                            $filenamePostfix = "X".$detailsParts[1];
                            $ratio = (int)$detailsParts[1];
                            break;
                        default:
                            throw new \RPI\Foundation\Exceptions\RuntimeException(
                                "Unknown option '{$detailsParts[0]} in '$inputFilename{$matches[3]["fileDetails"]}'"
                            );
                    }
                }
                
                $buildName = $build->name.$filenamePostfix;
                
                $spriteOutputFilename = dirname($outputFilename)."/I/Sprites/{$buildName}.png";
                $debugSpriteOutputFilename = null;
                if (isset($build->debugPath)) {
                    $debugSpriteOutputFilename = $build->debugPath."/I/Sprites/{$buildName}.png";
                }
                    
                $spriteFilename = $resolver->realpath($project, $matches[2][0]);
                if ($spriteFilename === false) {
                    $spriteFilename = realpath(dirname($inputFilename)."/".$matches[2][0]);
                }
                if (!file_exists($spriteFilename)) {
                    throw new \RPI\Foundation\Exceptions\RuntimeException(
                        "Unable to locate sprite image '{$matches[2][0]}'".
                        " in '$inputFilename{$matches[2]["fileDetails"]}'"
                    );
                } else {
                    if (!isset($sprites[$spriteFilename])) {
                        $project->getLogger()->debug(
                            "Creating Sprite image ' $spriteFilename'"
                        );
                        $imageDataSprite = getimagesize($spriteFilename);

                        $im = null;
                        $offsetX = 0;
                        $offsetY = 0;

                        if (file_exists($spriteOutputFilename)) {
                            $previousSprite =
                                \RPI\Utilities\ContentBuild\Processors\Sprites::findLastIcon($buildName, $sprites);
                            if ($previousSprite !== false) {
                                $offsetX = $previousSprite["offsetX"] + $previousSprite["width"] + $spritePadding;
                                $offsetY = $previousSprite["offsetY"];
                            }

                            $imageDataSpriteOutput = getimagesize($spriteOutputFilename);

                            $outputSpriteWidth = max($imageDataSpriteOutput[0], $offsetX + $imageDataSprite[0]);
                            $outputSpriteHeight = max($imageDataSpriteOutput[1], $offsetY + $imageDataSprite[1]);

                            if ($outputSpriteWidth > $maxSpriteWidth) {
                                $outputSpriteWidth = $imageDataSpriteOutput[0];
                                $outputSpriteHeight = $imageDataSpriteOutput[1]
                                    + $imageDataSprite[1]
                                    + $spritePadding;
                                $offsetX = 0;
                                $offsetY = $imageDataSpriteOutput[1] + $spritePadding;
                            }

                            $imOriginal = imagecreatefrompng($spriteOutputFilename);
                            imagealphablending($imOriginal, false);
                            imagesavealpha($imOriginal, true);

                            $im = imagecreatetruecolor($outputSpriteWidth + $spritePadding, $outputSpriteHeight);
                            imagealphablending($im, false);
                            imagesavealpha($im, true);
                            $alpha = imagecolorallocatealpha($im, 0, 0, 0, 127);
                            imagefill($im, 0, 0, $alpha);

                            imagecopy(
                                $im,
                                $imOriginal,
                                0,
                                0,
                                0,
                                0,
                                $imageDataSpriteOutput[0],
                                $imageDataSpriteOutput[1]
                            );

                            imagedestroy($imOriginal);
                        } else {
                            $im = imagecreatetruecolor($imageDataSprite[0], $imageDataSprite[1]);
                            imagealphablending($im, false);
                            imagesavealpha($im, true);
                            $alpha = imagecolorallocatealpha($im, 0, 0, 0, 127);
                            imagefill($im, 0, 0, $alpha);
                        }

                        if (isset($im)) {
                            $type = explode("/", $imageDataSprite["mime"]);
                            $type = $type[1];
                            $imageCreateFunction = "imagecreatefrom".$type;

                            $im2 = $imageCreateFunction($spriteFilename);
                            imagealphablending($im2, false);
                            imagesavealpha($im2, true);
                            imagecopy($im, $im2, $offsetX, $offsetY, 0, 0, $imageDataSprite[0], $imageDataSprite[1]);

                            if (!file_exists(dirname($spriteOutputFilename))) {
                                $oldumask = umask(0);
                                mkdir(dirname($spriteOutputFilename), 0755, true);
                                umask($oldumask);
                            }
                
                            imagepng($im, $spriteOutputFilename);
                            
                            if (isset($debugSpriteOutputFilename)) {
                                if (!file_exists(dirname($debugSpriteOutputFilename))) {
                                    $oldumask = umask(0);
                                    mkdir(dirname($debugSpriteOutputFilename), 0755, true);
                                    umask($oldumask);
                                }

                                copy($spriteOutputFilename, $debugSpriteOutputFilename);
                            }

                            imagedestroy($im);
                            imagedestroy($im2);
                        } else {
                            throw new \RPI\Foundation\Exceptions\RuntimeException(
                                "Unable to create sprite image ' $spriteFilename'"
                            );
                        }

                        $sprites[$spriteFilename] = array(
                            "width" => $imageDataSprite[0],
                            "height" => $imageDataSprite[1],
                            "offsetX" => $offsetX,
                            "offsetY" => $offsetY,
                            "buildName" => $buildName,
                            "spriteName" => $spriteOutputFilename,
                            "spriteDebugName" => $debugSpriteOutputFilename,
                            "spritePath" => substr($spriteOutputFilename, strlen(dirname($outputFilename)) + 1),
                            "originalName" => $spriteFilename,
                            "ratio" => $ratio
                        );
                    }
                }
            },
            $buffer
        );
            
        $this->processor->setMetadata("sprites", $sprites);
        
        return true;
    }
    
    public function process(
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $buffer
    ) {
        $sprites = $this->processor->getMetaData("sprites");
        if (isset($sprites)) {
            $project = $this->project;
            $buffer = \RPI\Foundation\Helpers\Utils::pregReplaceCallbackOffset(
                "/(sprite\:\s*url\s*\(\s*'*\"*(.*?)'*\"*\s*\)\s*(.*?);)/sim",
                function ($matches) use ($inputFilename, $sprites, $project, $resolver) {
                    $spriteImage = $resolver->realpath($project, $matches[2][0]);
                    if ($spriteImage === false) {
                        $spriteImage = realpath(dirname($inputFilename)."/".$matches[2][0]);
                    }
                    if (isset($sprites, $sprites[$spriteImage])) {
                        $spriteData = $sprites[$spriteImage];

                        $offsetX = $spriteData["offsetX"];
                        if ($offsetX > 0) {
                            $offsetX = $offsetX * -1;
                        }
                        $offsetY = $spriteData["offsetY"];
                        if ($offsetY > 0) {
                            $offsetY = $offsetY * -1;
                        }
                        
                        $width = (int)$spriteData["width"];
                        $height = (int)$spriteData["height"];
                        
                        $extraRules = "";
                        
                        $ratio = (int)$spriteData["ratio"];
                        
                        if ($ratio !== 1) {
                            $offsetX = $offsetX / $ratio;
                            $offsetY = $offsetY / $ratio;
                            $width = $width / $ratio;
                            $height = $height / $ratio;
                            
                            $spriteDetails = getimagesize($spriteData["spriteName"]);
                            $extraRules .= "background-size:".
                                ($spriteDetails[0] / $ratio)."px ".($spriteDetails[1] / $ratio)."px";
                        }

                        // This needs to be on a single line so that line number reporting
                        // does not break (e.g. when using firebug)
                        return "background:url({$spriteData["spritePath"]}) no-repeat {$offsetX}px {$offsetY}px;".
                            "width:{$width}px;height:{$height}px;content:'';{$extraRules}";
                    } else {
                        throw new \RPI\Foundation\Exceptions\RuntimeException(
                            "Unable to locate sprite image '{$matches[2][0]}'".
                            " in '$inputFilename{$matches[2]["fileDetails"]}'"
                        );
                    }

                    return "";
                },
                $buffer
            );
        }
        
        return $buffer;
    }
    
    public function complete()
    {
    }
    
    public function canProcessBuffer()
    {
        return true;
    }
    
    public static function findLastIcon($buildName, array $sprites)
    {
        $sprites = array_reverse($sprites);
        foreach ($sprites as $sprite) {
            if (isset($sprite["buildName"]) && $sprite["buildName"] == $buildName) {
                return $sprite;
            }
        }
        
        return false;
    }
}
