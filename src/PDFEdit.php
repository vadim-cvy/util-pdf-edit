<?php
namespace Cvy\PDFEdit;
use Exception;

abstract class PDFEdit
{
  protected $pdf;

  public function view_in_browser( string $file_name ) : void
  {
    $this->output( 'I', $file_name );
  }

  public function download( string $file_name ) : void
  {
    $this->output( 'D', $file_name );
  }

  public function save( string $destination_path ) : void
  {
    $this->output( 'F', $destination_path );
  }

  public function get_as_string() : string
  {
    return $this->output( 'S' );
  }

  /**
   * @param $dest Fpdi::output $dest arg. See http://www.fpdf.org/en/doc/output.htm for more details.
   * @param $file_name Fpdi::output $name arg. See http://www.fpdf.org/en/doc/output.htm for more details.
   */
  private function output( string $dest, string $file_name = null ) : string
  {
    if ( $file_name && strpos( $file_name, '.pdf' ) === false )
    {
      $file_name .= '.pdf';
    }

    $this->pdf = new PDF();

    $this->pdf->setSourceFile( $this->get_source_pdf_path() );

    $this->pdf->SetFont('Helvetica');
    $this->pdf->SetFontSize(12);
    $this->pdf->SetTextColor(0, 0, 0);

    $this->apply_edits();

    $output_result = $this->pdf->Output( $dest, $file_name );

    $this->pdf->close();

    return $dest === 'S' ? $output_result : '';
  }

  abstract protected function get_source_pdf_path() : string;

  private function apply_edits() : void
  {
    for ( $i = 1; $i <= $this->get_source_pdf_pages_number(); $i++ )
    {
      $this->pdf->AddPage();

      $this->pdf->useTemplate( $this->pdf->importPage($i) );

      $this->apply_page_edits( $i, $this->pdf );
    }
  }

  private function get_source_pdf_pages_number() : int
  {
    return $this->is_automatic_pages_number_allowed() ?
      $this->get_source_pdf_pages_number__automtic() :
      $this->get_source_pdf_pages_number__static();
  }

  abstract protected function is_automatic_pages_number_allowed() : bool;

  /**
   * Detects source PDF file page numbers.
   *
   * NOTE. IT WORKS FOR ABSOLUTELY MOST CASES BUT IT STILL MAY RETURN WRONG VALUE.
   *
   * TODO: replace with more reliable solution
   *
   * @return integer
   */
  private function get_source_pdf_pages_number__automtic() : int
  {
    $source_pdf_content = file_get_contents( $this->get_source_pdf_path() );

    return preg_match_all("/\/Page\W/", $source_pdf_content, $dummy );
  }

  protected function get_source_pdf_pages_number__static() : int
  {
    throw new Exception( 'This method is abstract and must be implemented!' );
  }

  abstract protected function apply_page_edits( int $page_number, PDF $page ) : void;
}