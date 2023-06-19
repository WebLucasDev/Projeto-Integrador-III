<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   setcookie('user_id', create_unique_id(), time() + 60*60*24*30, '/');
   header('location:index.php');
}

if(isset($_POST['check'])){

   $check_in = $_POST['check_in'];
   $check_in = filter_var($check_in, FILTER_SANITIZE_STRING);

   $total_rooms = 0;

   $check_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE check_in = ?");
   $check_bookings->execute([$check_in]);

   while($fetch_bookings = $check_bookings->fetch(PDO::FETCH_ASSOC)){
      $total_rooms += $fetch_bookings['rooms'];
   }

   // if the hotel has total 30 rooms 
   if($total_rooms >= 30){
      $warning_msg[] = 'os quartos não estão disponíveis';
   }else{
      $success_msg[] = 'os quartos estão disponíveis';
   }

}

if(isset($_POST['book'])){

   $booking_id = create_unique_id();
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $rooms = $_POST['rooms'];
   $rooms = filter_var($rooms, FILTER_SANITIZE_STRING);
   $check_in = $_POST['check_in'];
   $check_in = filter_var($check_in, FILTER_SANITIZE_STRING);
   $check_out = $_POST['check_out'];
   $check_out = filter_var($check_out, FILTER_SANITIZE_STRING);
   $adults = $_POST['adults'];
   $adults = filter_var($adults, FILTER_SANITIZE_STRING);
   $childs = $_POST['childs'];
   $childs = filter_var($childs, FILTER_SANITIZE_STRING);

   $total_rooms = 0;

   $check_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE check_in = ?");
   $check_bookings->execute([$check_in]);

   while($fetch_bookings = $check_bookings->fetch(PDO::FETCH_ASSOC)){
      $total_rooms += $fetch_bookings['rooms'];
   }

   if($total_rooms >= 30){
      $warning_msg[] = 'os quartos não estão disponíveis';
   }else{

      $verify_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE user_id = ? AND name = ? AND email = ? AND number = ? AND rooms = ? AND check_in = ? AND check_out = ? AND adults = ? AND childs = ?");
      $verify_bookings->execute([$user_id, $name, $email, $number, $rooms, $check_in, $check_out, $adults, $childs]);

      if($verify_bookings->rowCount() > 0){
         $warning_msg[] = 'quarto já reservado!';
      }else{
         $book_room = $conn->prepare("INSERT INTO `bookings`(booking_id, user_id, name, email, number, rooms, check_in, check_out, adults, childs) VALUES(?,?,?,?,?,?,?,?,?,?)");
         $book_room->execute([$booking_id, $user_id, $name, $email, $number, $rooms, $check_in, $check_out, $adults, $childs]);
         $success_msg[] = 'quarto reservado com sucesso!';
      }

   }

}

if(isset($_POST['send'])){

   $id = create_unique_id();
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $message = $_POST['message'];
   $message = filter_var($message, FILTER_SANITIZE_STRING);

   $verify_message = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
   $verify_message->execute([$name, $email, $number, $message]);

   if($verify_message->rowCount() > 0){
      $warning_msg[] = 'mensagem já enviada!';
   }else{
      $insert_message = $conn->prepare("INSERT INTO `messages`(id, name, email, number, message) VALUES(?,?,?,?,?)");
      $insert_message->execute([$id, $name, $email, $number, $message]);
      $success_msg[] = 'mensagem enviada com sucesso!';
   }

}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Sejam todos bem-vindos</title>

   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>

<!-- home section starts  -->

<section class="home" id="home">

   <div class="swiper home-slider">

      <div class="swiper-wrapper">

         <div class="box swiper-slide">
            <img src="images/home-img-1.jpg" alt="">
            <div class="flex">
               <h3>Quartos luxuosos</h3>
               <a href="#availability" class="btn">Verificar disponibilidade</a>
            </div>
         </div>

         <div class="box swiper-slide">
            <img src="images/home-img-2.jpg" alt="">
            <div class="flex">
               <h3>Cardápio fenomenal</h3>
               <a href="#reservation" class="btn">Fazer uma reserva</a>
            </div>
         </div>

         <div class="box swiper-slide">
            <img src="images/home-img-3.jpg" alt="">
            <div class="flex">
               <h3>Espaço super aconchegante</h3>
               <a href="#contact" class="btn">Fale conosco</a>
            </div>
         </div>

      </div>

      <div class="swiper-button-next"></div>
      <div class="swiper-button-prev"></div>

   </div>

</section>

<!-- home section ends -->

<!-- availability section starts  -->

<section class="availability" id="availability">

   <form action="" method="post">
      <div class="flex">
         <div class="box">
            <p>Entrada <span>*</span></p>
            <input type="date" name="check_in" class="input" required>
         </div>
         <div class="box">
            <p>Saída <span>*</span></p>
            <input type="date" name="check_out" class="input" required>
         </div>
         <div class="box">
            <p>Adultos <span>*</span></p>
            <select name="adults" class="input" required>
               <option value="1">1 adulto</option>
               <option value="2">2 adultos</option>
               <option value="3">3 adultos</option>
               <option value="4">4 adultos</option>
               <option value="5">5 adultos</option>
               <option value="6">6 adultos</option>
            </select>
         </div>
         <div class="box">
            <p>Crianças <span>*</span></p>
            <select name="childs" class="input" required>
               <option value="-">0 crianças</option>
               <option value="1">1 crianças</option>
               <option value="2">2 crianças</option>
               <option value="3">3 crianças</option>
               <option value="4">4 crianças</option>
               <option value="5">5 crianças</option>
               <option value="6">6 crianças</option>
            </select>
         </div>
         <div class="box">
            <p>Quartos <span>*</span></p>
            <select name="rooms" class="input" required>
               <option value="1">1 quarto</option>
               <option value="2">2 quartos</option>
               <option value="3">3 quartos</option>
               <option value="4">4 quartos</option>
               <option value="5">5 quartos</option>
               <option value="6">6 quartos</option>
            </select>
         </div>
      </div>
      <input type="submit" value="verifique se está disponível" name="check" class="btn">
   </form>

</section>

<!-- availability section ends -->

<!-- about section starts  -->

<section class="about" id="about">

   <div class="row">
      <div class="image">
         <img src="images/about-img-1.jpg" alt="">
      </div>
      <div class="content">
         <h3>A melhor equipe para lhe atender</h3>
         <p>Nossa equipe está de braços abertos para lhe receber, você se sentirá em casa! Possuimos uma equipe totalmente preparada para lhe atender e proporcionar a melhor experiência possível.</p>
         <a href="#reservation" class="btn">fazer uma reserva</a>
      </div>
   </div>

   <div class="row revers">
      <div class="image">
         <img src="images/about-img-2.jpg" alt="">
      </div>
      <div class="content">
         <h3>Gastronomia sofisticada</h3>
         <p>Temos um grande banquete a sua espera, com direito há o melhor rodízio de carnes da região!</p>
         <a href="#contact" class="btn">fale conosco</a>
      </div>
   </div>

   <div class="row">
      <div class="image">
         <img src="images/about-img-3.jpg" alt="">
      </div>
      <div class="content">
         <h3>Lazer</h3>
         <p>Nosso espaço conta com uma área de lazer muito especial e divertida para as crianças brincarem a vontade!</p>
         <a href="#availability" class="btn">verificar disponibilidade</a>
      </div>
   </div>

</section>

<!-- about section ends -->

<!-- services section starts  -->

<section class="services">

   <div class="box-container">

      <div class="box">
         <img src="images/icon-1.png" alt="">
         <h3>Comidas e Bebidas</h3>
         <p>Temos o melhor café da manhã,o mais gostoso almoço e a mais saborosa janta em todos os dias da semana!</p>
      </div>

      <div class="box">
         <img src="images/icon-2.png" alt="">
         <h3>Espaço ao ar livre</h3>
         <p>Nossa cobertura consta com um incrível espaço ao ar livre para poder se sentir leve e traquilo (a)!</p>
      </div>

      <div class="box">
         <img src="images/icon-3.png" alt="">
         <h3>Vista encantadora</h3>
         <p>Possuimos uma vista deslumbrante de toda a cidade em nossa cobertura.</p>
      </div>

      <div class="box">
         <img src="images/icon-4.png" alt="">
         <h3>Decoração sofisticada</h3>
         <p>Predominamos um estilo sofisticado de toda a nossa decoração que o hotel pode lhe oferecer!</p>
      </div>

      <div class="box">
         <img src="images/lazericon.png" alt="">
         <h3>Diversão para os pequenos</h3>
         <p>Nosso espaço consta com uma área de lazer muito especial e divertida para as crianças brincarem a vontade!</p>
      </div>

      <div class="box">
         <img src="images/grade.png" alt="">
         <h3>Rodízio de carne</h3>
         <p>Uma de nossas especialidades é o espetacular rodízio de carnes totalmente selecionadas, para lhe proporcionar o melhor churrasco da vida!</p>
      </div>

   </div>

</section>

<!-- services section ends -->

<!-- reservation section starts  -->

<section class="reservation" id="reservation">

   <form action="" method="post">
      <h3>Fazer uma reserva</h3>
      <div class="flex">
         <div class="box">
            <p>seu nome <span>*</span></p>
            <input type="text" name="name" maxlength="50" required placeholder="digite seu nome" class="input">
         </div>
         <div class="box">
            <p>seu email <span>*</span></p>
            <input type="email" name="email" maxlength="50" required placeholder="digite seu email" class="input">
         </div>
         <div class="box">
            <p>seu número de telefone <span>*</span></p>
            <input type="number" name="number" maxlength="12" min="0" max="9999999999" required placeholder="DDD 0000-0000" class="input">
         </div>
         <div class="box">
            <p>quartos <span>*</span></p>
            <select name="rooms" class="input" required>
               <option value="1" selected>1 quarto</option>
               <option value="2">2 quartos</option>
               <option value="3">3 quartos</option>
               <option value="4">4 quartos</option>
               <option value="5">5 quartos</option>
               <option value="6">6 quartos</option>
            </select>
         </div>
         <div class="box">
            <p>entrada <span>*</span></p>
            <input type="date" name="check_in" class="input" required>
         </div>
         <div class="box">
            <p>saída <span>*</span></p>
            <input type="date" name="check_out" class="input" required>
         </div>
         <div class="box">
            <p>adultos <span>*</span></p>
            <select name="adults" class="input" required>
               <option value="1" selected>1 adulto</option>
               <option value="2">2 adultos</option>
               <option value="3">3 adultos</option>
               <option value="4">4 adultos</option>
               <option value="5">5 adultos</option>
               <option value="6">6 adultos</option>
            </select>
         </div>
         <div class="box">
            <p>crianças <span>*</span></p>
            <select name="childs" class="input" required>
               <option value="0" selected>0 crianças</option>
               <option value="1">1 criança</option>
               <option value="2">2 crianças</option>
               <option value="3">3 crianças</option>
               <option value="4">4 crianças</option>
               <option value="5">5 crianças</option>
               <option value="6">6 crianças</option>
            </select>
         </div>
      </div>
      <input type="submit" value="agende agora" name="book" class="btn">
   </form>

</section>

<!-- reservation section ends -->

<!-- gallery section starts  -->

<section class="gallery" id="gallery">

   <div class="swiper gallery-slider">
      <div class="swiper-wrapper">
         <img src="images/gallery-img-1.jpg" class="swiper-slide" alt="">
         <img src="images/gallery-img-2.webp" class="swiper-slide" alt="">
         <img src="images/gallery-img-3.webp" class="swiper-slide" alt="">
         <img src="images/gallery-img-4.webp" class="swiper-slide" alt="">
         <img src="images/gallery-img-5.webp" class="swiper-slide" alt="">
         <img src="images/gallery-img-6.webp" class="swiper-slide" alt="">
      </div>
      <div class="swiper-pagination"></div>
   </div>

</section>

<!-- gallery section ends -->

<!-- contact section starts  -->

<section class="contact" id="contact">

   <div class="row">

      <form action="" method="post">
         <a href="#mensagem"><h3>Envie-nos uma mensagem</h3></a>
         <input type="text" name="name" required maxlength="50" placeholder="Digite seu nome" class="box">
         <input type="email" name="email" required maxlength="50" placeholder="Digite seu email" class="box">
         <input type="number" name="number" required maxlength="12" min="0" max="9999999999" placeholder="Digite seu número" class="box">
         <textarea name="message" class="box" required maxlength="1000" placeholder="Escreva sua mensagem aqui, porfavor!" cols="30" rows="10"></textarea>
         <input type="submit" value="enviar mensagem" name="send" class="btn">
      </form>

      <div class="faq">
         <h3 class="title">Perguntas frequentes</h3>
         <div class="box active">
            <h3>Como realizar uma cancelamento?</h3>
            <p>Para realizar o cancelamento de sua reserva, envie-nos uma  através do campo ao lado, ou entre em contato pelo telefone: <a href="tel:+5503438263300">(34) 3826-3300.</a></p>
         </div>
         <div class="box">
            <h3>Como saber se há algum quarto disponível?</h3>
            <p>Para checar a disponibilidade de algum quarto no período em que deseja, faça uma checagem de disponibilidade através da ferramenta no início da página. Caso prefira, entre em contato conosco através do telefone: <a href="tel:+5503438263300">(34) 3826-3300.</a></p>
         </div>
         <div class="box">
            <h3>Quais os métodos de pagamentos?</h3>
            <p>Aceitamos as seguintes formas de pagamento: dinheiro, Pix, cartões de crédito e débito.</p>
         </div>
         <div class="box">
            <h3>Como entrar para o clube de benefícios Gálatas Golden?</h3>
            <p>O clube de benefícios Gálatas Goldens é uma forma de possuir diversas vantagens e preços exclusivos em reservas. Para saber mais sobre, entre em contato conosco através do telefone:  <a href="tel:+5503438263300">(34) 3826-3300.</a></p>
         </div>
         <div class="box">
            <h3>Quais os requisitos de idade?</h3>
            <p>Menores de 16 anos só podem se hospedar perante a presença dos pais e/ou responsáveis. Jovens entre 16 e 17 anos podem se hospedar sozinhos, caso tenham autorização dos pais ou responsáveis, por escrito e formalizada em cartório.</p>
         </div>
      </div>

   </div>

</section>

<!-- contact section ends -->

<!-- reviews section starts  -->

<section class="reviews" id="reviews">

   <div class="swiper reviews-slider">

      <div class="swiper-wrapper">
         <div class="swiper-slide box">
            <img src="images/pic-1.png" alt="">
            <h3>john deo</h3>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptates blanditiis optio dignissimos eaque aliquid explicabo.</p>
         </div>
         <div class="swiper-slide box">
            <img src="images/pic-2.png" alt="">
            <h3>john deo</h3>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptates blanditiis optio dignissimos eaque aliquid explicabo.</p>
         </div>
         <div class="swiper-slide box">
            <img src="images/pic-3.png" alt="">
            <h3>john deo</h3>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptates blanditiis optio dignissimos eaque aliquid explicabo.</p>
         </div>
         <div class="swiper-slide box">
            <img src="images/pic-4.png" alt="">
            <h3>john deo</h3>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptates blanditiis optio dignissimos eaque aliquid explicabo.</p>
         </div>
         <div class="swiper-slide box">
            <img src="images/pic-5.png" alt="">
            <h3>john deo</h3>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptates blanditiis optio dignissimos eaque aliquid explicabo.</p>
         </div>
         <div class="swiper-slide box">
            <img src="images/pic-6.png" alt="">
            <h3>john deo</h3>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptates blanditiis optio dignissimos eaque aliquid explicabo.</p>
         </div>
      </div>

      <div class="swiper-pagination"></div>
   </div>

</section>

<!-- reviews section ends  -->

<?php include 'components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<?php include 'components/message.php'; ?>

</body>
</html>