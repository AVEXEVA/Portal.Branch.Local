<?php
namespace dom\header;
class administrator {
  public function __construct( ){
    ?><style>
      .nav-role.administrator .nav-item {
        min-width:125px;
      }
    </style>
    <div class='nav-role administrator'>
      <nav <?php new \dom\nav\attributes( 'light', 'h75px' );?>>
        <a class="navbar-brand" href="#"><?php new \icon\admin( );?> Administrator</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav mr-auto">
            <?php new \dom\nav\item\organization( );?>
            <?php new \dom\nav\item\role( );?>
            <?php new \dom\nav\item\customer( );?>
            <?php new \dom\nav\item\location( );?>
            <?php new \dom\nav\item\unit( );?>
            <?php new \dom\nav\item\job( );?>
            <?php new \dom\nav\item\dom( );?>
          </ul>
        </div>
      </nav>
      <script> $('.dropdown-menu').click(function(e) { e.stopPropagation(); }); </script>
    </div><?php 
  }
}?>