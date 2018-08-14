<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Apidae\TwigExtensions;


class Paginate {
	public static function PaginationDataFunction( $totalPages, $currentPage, $length = 11 ) {
		$p = new Paginate( $length );

		return $p->getPaginationData( $currentPage, $totalPages, $length );
	}

	/**
	 * @param string $link The link to put in the pagination with the %PAGE% string in it that will be replaced by the number of the page
	 * @param int $totalPages
	 * @param int $currentPage
	 * @param int $length Maximum length of the pagination (doesn't include next or prev)
	 * @param bool|string $prev Previous Symbol
	 * @param bool|string $next Next Symbol
	 *
	 * @return string
	 */
	public static function PaginateFunction( $link, $totalPages, $currentPage, $length = 11, $prev = false, $next = false ) {
		global $WPlusPlusApidae;
		$p    = new Paginate( $length );
		$data = $p->getPaginationData( $currentPage, $totalPages );
		$html = "<ul class='pagination'>";
		if ( ! empty( $prev ) && $currentPage > 1 ) {
			$l    = esc_attr( str_replace( '%PAGE%', $currentPage - 1, $link ) );
			$html .= "<li class='prev' title='" . __( 'Previous', $WPlusPlusApidae->getTextDomain() ) . "'><a href='$l'>" . esc_html( $prev ) . "</a></li>";
		} elseif ( ! empty( $prev ) && $currentPage == 1 ) {
			$html .= "<li class='prev disabled'><span class='a-placeholder'>" . esc_html( $prev ) . "</span></li>";
		}
		foreach ( $data as $datum ) {
			if ( $datum == - 1 ) {
				$html .= "<li class='hellip disabled'>&hellip;</li>";
			} elseif ( $datum == $currentPage ) {
				$html .= "<li class='page-$datum current'><span class='a-placeholder'>$datum</span></li>";
			} elseif ( $datum >= 1 ) {
				$l    = esc_attr( str_replace( '%PAGE%', $datum, $link ) );
				$html .= "<li class='page-$datum' title='" . sprintf( __( 'Go to page %d', $WPlusPlusApidae->getTextDomain() ), $datum ) . "'><a href='$l'>$datum</a></li>";
			}
		}
		if ( ! empty( $next ) && $currentPage < $totalPages ) {
			$l    = esc_attr( str_replace( '%PAGE%', $currentPage + 1, $link ) );
			$html .= "<li class='next' title='" . __( 'Next', $WPlusPlusApidae->getTextDomain() ) . "'><a href='$l'>" . esc_html( $next ) . "</a></li>";
		} elseif ( ! empty( $next ) && $currentPage >= $totalPages ) {
			$html .= "<li class='next disabled'><span class='a-placeholder'>" . esc_html( $next ) . "</span></li>";
		}
		$html .= '</ul>';

		return $html;
	}


	/**
	 * @var int
	 */
	private $maximumVisible;

	/**
	 * @param int $maximumVisible
	 *   Maximum number of visible pages. Should never be lower than 7.
	 *   1 on each edge, 1 omitted chunk on each side, and 3 in the middle.
	 *   For example: [1][...][11][12][13][...][20]
	 */
	public function __construct( $maximumVisible ) {
		$this->setMaximumVisible( $maximumVisible );
	}

	/**
	 * @param int $maximumVisible
	 *
	 * @return static
	 */
	public function withMaximumVisible( $maximumVisible ) {
		$c = clone $this;
		$c->setMaximumVisible( $maximumVisible );

		return $c;
	}

	/**
	 * @param int $maximumVisible
	 */
	private function setMaximumVisible( $maximumVisible ) {
		$maximumVisible = (int) $maximumVisible;
		$this->guardMaximumVisibleMinimumValue( $maximumVisible );
		$this->maximumVisible = $maximumVisible;
	}

	/**
	 * @return int
	 */
	public function getMaximumVisible() {
		return $this->maximumVisible;
	}

	/**
	 * @param $maximumVisible
	 *
	 * @throws \InvalidArgumentException
	 *   If the maximum number of visible pages is lower than 7.
	 */
	private function guardMaximumVisibleMinimumValue( $maximumVisible ) {
		// Maximum number of allowed visible pages should never be lower than 7.
		// 1 on each edge, 1 omitted chunk on each side, and 3 in the middle.
		// For example: [1][...][11][12][13][...][20]
		if ( $maximumVisible < 7 ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Maximum of number of visible pages (%d) should be at least 7.',
					$maximumVisible
				)
			);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getPaginationData( $currentPage, $totalPages, $omittedPagesIndicator = - 1 ) {
		$this->guardPaginationData( $totalPages, $currentPage, $omittedPagesIndicator );

		// If the total number of pages is less than the maximum number of
		// allowed visible pages, we don't need to omit anything.
		if ( $totalPages <= $this->maximumVisible ) {
			return $this->getPaginationDataWithNoOmittedChunks( $totalPages );
		}

		// Check if we can omit a single chunk of pages, depending on the
		// position of the current page relative to the first and last page.
		if ( $this->hasSingleOmittedChunk( $totalPages, $currentPage ) ) {
			return $this->getPaginationDataWithSingleOmittedChunk( $totalPages, $currentPage, $omittedPagesIndicator );
		}

		// Otherwise omit two chunks of pages, one on each side of the current
		// page.
		return $this->getPaginationDataWithTwoOmittedChunks( $totalPages, $currentPage, $omittedPagesIndicator );
	}

	/**
	 * @param int $totalPages
	 *
	 * @return array
	 */
	private function getPaginationDataWithNoOmittedChunks( $totalPages ) {
		return range( 1, $totalPages );
	}

	/**
	 * @return int
	 */
	private function getSingleOmissionBreakpoint() {
		return (int) floor( $this->maximumVisible / 2 ) + 1;
	}

	/**
	 * @param int $totalPages
	 * @param int $currentPage
	 *
	 * @return bool
	 */
	public function hasSingleOmittedChunk( $totalPages, $currentPage ) {
		return $this->hasSingleOmittedChunkNearLastPage( $currentPage ) ||
		       $this->hasSingleOmittedChunkNearStartPage( $totalPages, $currentPage );
	}

	/**
	 * @param int $currentPage
	 *
	 * @return bool
	 */
	private function hasSingleOmittedChunkNearLastPage( $currentPage ) {
		return $currentPage <= $this->getSingleOmissionBreakpoint();
	}

	/**
	 * @param int $totalPages
	 * @param int $currentPage
	 *
	 * @return bool
	 */
	private function hasSingleOmittedChunkNearStartPage( $totalPages, $currentPage ) {
		return $currentPage >= $totalPages - $this->getSingleOmissionBreakpoint() + 1;
	}

	/**
	 * @param int $totalPages
	 * @param int $currentPage
	 * @param int|string $omittedPagesIndicator
	 *
	 * @return array
	 */
	private function getPaginationDataWithSingleOmittedChunk( $totalPages, $currentPage, $omittedPagesIndicator ) {
		// Determine where the omitted chunk of pages will be.
		if ( $this->hasSingleOmittedChunkNearLastPage( $currentPage ) ) {
			$rest          = $this->maximumVisible - $currentPage;
			$omitPagesFrom = ( (int) ceil( $rest / 2 ) ) + $currentPage;
			$omitPagesTo   = $totalPages - ( $this->maximumVisible - $omitPagesFrom );
		} else {
			$rest          = $this->maximumVisible - ( $totalPages - $currentPage );
			$omitPagesFrom = (int) ceil( $rest / 2 );
			$omitPagesTo   = ( $currentPage - ( $rest - $omitPagesFrom ) );
		}

		// Fill each side of the pagination data, around the omitted chunk of
		// pages.
		$pagesLeft  = range( 1, $omitPagesFrom - 1 );
		$pagesRight = range( $omitPagesTo + 1, $totalPages );

		// Merge left side, omitted pages indicator, and right side together.
		return array_merge(
			$pagesLeft,
			[ $omittedPagesIndicator ],
			$pagesRight
		);
	}

	/**
	 * @param int $totalPages
	 * @param int $currentPage
	 * @param int|string $omittedPagesIndicator
	 *
	 * @return array
	 */
	private function getPaginationDataWithTwoOmittedChunks( $totalPages, $currentPage, $omittedPagesIndicator ) {
		$visibleExceptForCurrent = $this->maximumVisible - 1;

		if ( $currentPage <= ceil( $totalPages / 2 ) ) {
			$visibleLeft  = ceil( $visibleExceptForCurrent / 2 );
			$visibleRight = floor( $visibleExceptForCurrent / 2 );
		} else {
			$visibleLeft  = floor( $visibleExceptForCurrent / 2 );
			$visibleRight = ceil( $visibleExceptForCurrent / 2 );
		}

		// Put the left chunk of omitted pages in the middle of the visible
		// pages to the left of the current page.
		$omitPagesLeftFrom = floor( $visibleLeft / 2 ) + 1;
		$omitPagesLeftTo   = $currentPage - ( $visibleLeft - $omitPagesLeftFrom ) - 1;

		// Put the right chunk of omitted pages in the middle of the visible
		// pages to the right of the current page.
		$omitPagesRightFrom = ceil( $visibleRight / 2 ) + $currentPage;
		$omitPagesRightTo   = $totalPages - ( $visibleRight - ( $omitPagesRightFrom - $currentPage ) );

		// Fill the left side of pages up to the first omitted chunk, the pages
		// in the middle up to the second omitted chunk, and the right side of
		// pages.
		$pagesLeft   = range( 1, $omitPagesLeftFrom - 1 );
		$pagesCenter = range( $omitPagesLeftTo + 1, $omitPagesRightFrom - 1 );
		$pagesRight  = range( $omitPagesRightTo + 1, $totalPages );

		// Merge everything together with omitted chunks of pages in between
		// them.
		return array_merge(
			$pagesLeft,
			[ $omittedPagesIndicator ],
			$pagesCenter,
			[ $omittedPagesIndicator ],
			$pagesRight
		);
	}

	/**
	 * @param int $totalPages
	 * @param int $currentPage
	 * @param int|string $omittedPagesIndicator
	 *
	 * @throws \InvalidArgumentException
	 *   When pagination data is invalid.
	 */
	protected function guardPaginationData( $totalPages, $currentPage, $omittedPagesIndicator = - 1 ) {
		$this->guardTotalPagesMinimumValue( $totalPages );
		$this->guardCurrentPageMinimumValue( $currentPage );
		$this->guardCurrentPageExistsInTotalPages( $totalPages, $currentPage );
		$this->guardOmittedPagesIndicatorType( $omittedPagesIndicator );
		$this->guardOmittedPagesIndicatorIntValue( $totalPages, $omittedPagesIndicator );
	}

	/**
	 * @param int $totalPages
	 *
	 * @throws \InvalidArgumentException
	 *   If total number of pages is lower than 1.
	 */
	private function guardTotalPagesMinimumValue( $totalPages ) {
		if ( $totalPages < 1 ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Total number of pages (%d) should not be lower than 1.',
					$totalPages
				)
			);
		}
	}

	/**
	 * @param int $currentPage
	 *
	 * @throws \InvalidArgumentException
	 *   If current page is lower than 1.
	 */
	private function guardCurrentPageMinimumValue( $currentPage ) {
		if ( $currentPage < 1 ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Current page (%d) should not be lower than 1.',
					$currentPage
				)
			);
		}
	}

	/**
	 * @param int $totalPages
	 * @param int $currentPage
	 *
	 * @throws \InvalidArgumentException
	 *   If current page is higher than total number of pages.
	 */
	private function guardCurrentPageExistsInTotalPages( $totalPages, $currentPage ) {
		if ( $currentPage > $totalPages ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Current page (%d) should not be higher than total number of pages (%d).',
					$currentPage,
					$totalPages
				)
			);
		}
	}

	/**
	 * @param int|string $indicator
	 *
	 * @throws \InvalidArgumentException
	 *   If omitted pages indicator is not an int or a string.
	 */
	private function guardOmittedPagesIndicatorType( $indicator ) {
		if ( ! is_int( $indicator ) && ! is_string( $indicator ) ) {
			throw new \InvalidArgumentException(
				'Omitted pages indicator should either be a string or an int.'
			);
		}
	}

	/**
	 * @param int $totalPages
	 * @param int|string $indicator
	 *
	 * @throws \InvalidArgumentException
	 *   If omitted pages indicator is an int in the range of 1 and the total
	 *   number of pages.
	 */
	private function guardOmittedPagesIndicatorIntValue( $totalPages, $indicator ) {
		if ( is_int( $indicator ) && $indicator >= 1 && $indicator <= $totalPages ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Omitted pages indicator (%d) should not be between 1 and total number of pages (%d).',
					$indicator,
					$totalPages
				)
			);
		}
	}
}