<?php
namespace Cvy\PDFEdit;

/**
 * Wrapper for Fpdi class
 *
 * Rotation methods/props are copied from here: https://stackoverflow.com/questions/40770136/rotate-text-in-fpdffpdi
 */
class PDF extends \setasign\Fpdi\Fpdi
{
  private $angle = 0;

  public function Rotate( int $angle, int $orgin_x = -1, int $origin_y = -1 ) : void
  {
    if($orgin_x==-1)
      $orgin_x=$this->x;
    if($origin_y==-1)
      $origin_y=$this->y;
    if($this->angle!=0)
      $this->_out('Q');
    $this->angle=$angle;
    if($angle!=0)
    {
      $angle*=M_PI/180;
      $c=cos($angle);
      $s=sin($angle);
      $cx=$orgin_x*$this->k;
      $cy=($this->h-$origin_y)*$this->k;
      $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
    }
  }

  public function _endpage()
  {
    if($this->angle!=0)
    {
      $this->angle=0;
      $this->_out('Q');
    }
    parent::_endpage();
  }

  /**
   * Writes rotated text
   *
   * Text is rotated around its origin
   */
  public function WriteRotated( int $x, int $y, string $txt, int $angle ) : void
  {
    $this->Rotate($angle,$x,$y);
    $this->Text($x,$y,$txt);
    $this->Rotate(0);
  }

  /**
   * Puts rotated image
   *
   * Text is rotated around its upper-left corner
   */
  public function ImageRotated( string $path, int $x, int $y, int $w, int $h, int $angle ) : void
  {
    $this->Rotate($angle,$x,$y);
    $this->Image($path,$x,$y,$w,$h);
    $this->Rotate(0);
  }
}