<?php
namespace QRCode\Library;

use QRCode\Library\Bitstream;
use QRCode\Library\Input;
use Exception;

class InputItem
{

    public $mode;
    public $size;
    public $data;
    public $bstream;

    public function __construct($mode, $size, $data, $bstream = null)
    {
        $setData = array_slice($data, 0, $size);

        if (count($setData) < $size) {
            $setData = array_merge($setData, array_fill(0, $size - count($setData), 0));
        }

        if (!Input::check($mode, $size, $setData)) {
            throw new \Exception('Error m:' . $mode . ',s:' . $size . ',d:' . join(',', $setData), 500);
            // return null;
        }

        $this->mode = $mode;
        $this->size = $size;
        $this->data = $setData;
        $this->bstream = $bstream;
    }

    //----------------------------------------------------------------------
    public function encodeModeNum($version)
    {
        try {

            $words = (int) ($this->size / 3);
            $bs = new Bitstream();

            $val = 0x1;
            $bs->appendNum(4, $val);
            $bs->appendNum(Spec::lengthIndicator(QR_MODE_NUM, $version), $this->size);

            for ($i = 0; $i < $words; $i++) {
                $val = (ord($this->data[$i * 3]) - ord('0')) * 100;
                $val += (ord($this->data[$i * 3 + 1]) - ord('0')) * 10;
                $val += (ord($this->data[$i * 3 + 2]) - ord('0'));
                $bs->appendNum(10, $val);
            }

            if ($this->size - $words * 3 == 1) {
                $val = ord($this->data[$words * 3]) - ord('0');
                $bs->appendNum(4, $val);
            } else if ($this->size - $words * 3 == 2) {
                $val = (ord($this->data[$words * 3]) - ord('0')) * 10;
                $val += (ord($this->data[$words * 3 + 1]) - ord('0'));
                $bs->appendNum(7, $val);
            }

            $this->bstream = $bs;
            return 0;

        } catch (\Exception $e) {
            return -1;
        }
    }

    //----------------------------------------------------------------------
    public function encodeModeAn($version)
    {
        try {
            $words = (int) ($this->size / 2);
            $bs = new Bitstream();

            $bs->appendNum(4, 0x02);
            $bs->appendNum(Spec::lengthIndicator(QR_MODE_AN, $version), $this->size);

            for ($i = 0; $i < $words; $i++) {
                $val = (int) Input::lookAnTable(ord($this->data[$i * 2])) * 45;
                $val += (int) Input::lookAnTable(ord($this->data[$i * 2 + 1]));

                $bs->appendNum(11, $val);
            }

            if ($this->size & 1) {
                $val = Input::lookAnTable(ord($this->data[$words * 2]));
                $bs->appendNum(6, $val);
            }

            $this->bstream = $bs;
            return 0;

        } catch (Exception $e) {
            return -1;
        }
    }

    //----------------------------------------------------------------------
    public function encodeMode8($version)
    {
        try {
            $bs = new Bitstream();

            $bs->appendNum(4, 0x4);
            $bs->appendNum(Spec::lengthIndicator(QR_MODE_8, $version), $this->size);

            for ($i = 0; $i < $this->size; $i++) {
                $bs->appendNum(8, ord($this->data[$i]));
            }

            $this->bstream = $bs;
            return 0;

        } catch (Exception $e) {
            return -1;
        }
    }

    //----------------------------------------------------------------------
    public function encodeModeKanji($version)
    {
        try {

            $bs = new Bitstream();

            $bs->appendNum(4, 0x8);
            $bs->appendNum(Spec::lengthIndicator(QR_MODE_KANJI, $version), (int) ($this->size / 2));

            for ($i = 0; $i < $this->size; $i += 2) {
                $val = (ord($this->data[$i]) << 8) | ord($this->data[$i + 1]);
                if ($val <= 0x9ffc) {
                    $val -= 0x8140;
                } else {
                    $val -= 0xc140;
                }

                $h = ($val >> 8) * 0xc0;
                $val = ($val & 0xff) + $h;

                $bs->appendNum(13, $val);
            }

            $this->bstream = $bs;
            return 0;

        } catch (Exception $e) {
            return -1;
        }
    }

    //----------------------------------------------------------------------
    public function encodeModeStructure()
    {
        try {
            $bs = new Bitstream();

            $bs->appendNum(4, 0x03);
            $bs->appendNum(4, ord($this->data[1]) - 1);
            $bs->appendNum(4, ord($this->data[0]) - 1);
            $bs->appendNum(8, ord($this->data[2]));

            $this->bstream = $bs;
            return 0;

        } catch (Exception $e) {
            return -1;
        }
    }

    //----------------------------------------------------------------------
    public function estimateBitStreamSizeOfEntry($version)
    {
        $bits = 0;

        if ($version == 0)
            $version = 1;

        switch ($this->mode) {
            case QR_MODE_NUM:
                $bits = Input::estimateBitsModeNum($this->size);
                break;
            case QR_MODE_AN:
                $bits = Input::estimateBitsModeAn($this->size);
                break;
            case QR_MODE_8:
                $bits = Input::estimateBitsMode8($this->size);
                break;
            case QR_MODE_KANJI:
                $bits = Input::estimateBitsModeKanji((int)$this->size);
                break;
            case QR_MODE_STRUCTURE:
                return STRUCTURE_HEADER_BITS;
            default:
                return 0;
        }

        $l = Spec::lengthIndicator($this->mode, $version);
        $m = 1 << $l;
        $num = (int) (($this->size + $m - 1) / $m);

        $bits += $num * (4 + $l);

        return $bits;
    }

    //----------------------------------------------------------------------
    public function encodeBitStream($version)
    {
        try {

            unset($this->bstream);
            $words = Spec::maximumWords($this->mode, $version);

            if ($this->size > $words) {

                $st1 = new InputItem($this->mode, $words, $this->data);
                $st2 = new InputItem($this->mode, $this->size - $words, array_slice($this->data, $words));

                $st1->encodeBitStream($version);
                $st2->encodeBitStream($version);

                $this->bstream = new Bitstream();
                $this->bstream->append($st1->bstream);
                $this->bstream->append($st2->bstream);

                unset($st1);
                unset($st2);

            } else {

                $ret = 0;

                switch ($this->mode) {
                    case QR_MODE_NUM:
                        $ret = $this->encodeModeNum($version);
                        break;
                    case QR_MODE_AN:
                        $ret = $this->encodeModeAn($version);
                        break;
                    case QR_MODE_8:
                        $ret = $this->encodeMode8($version);
                        break;
                    case QR_MODE_KANJI:
                        $ret = $this->encodeModeKanji($version);
                        break;
                    case QR_MODE_STRUCTURE:
                        $ret = $this->encodeModeStructure();
                        break;

                    default:
                        break;
                }

                if ($ret < 0)
                    return -1;
            }

            return $this->bstream->size();

        } catch (Exception $e) {
            return -1;
        }
    }
}
;