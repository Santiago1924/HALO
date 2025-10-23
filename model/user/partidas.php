<?php
      // Conexión a la base de datos
      $conn = new mysqli("localhost", "root", "", "halo_style");
      if ($conn->connect_error) {
          die("Error de conexión: " . $conn->connect_error);
      }

      // Obtener ID del usuario logueado
      $queryUser = $conn->prepare("SELECT id_user FROM users WHERE username = ?");
      $queryUser->bind_param("s", $username);
      $queryUser->execute();
      $resultUser = $queryUser->get_result();
      $userData = $resultUser->fetch_assoc();
      $userId = $userData['id_user'] ?? 0;

      // Consultar partidas jugadas y ganadas
      $sql = "
        SELECT 
          COUNT(DISTINCT rp.id_games) AS total_partidas,
          SUM(CASE WHEN ge.event_type = 'win' THEN 1 ELSE 0 END) AS partidas_ganadas
        FROM room_players rp
        INNER JOIN games g ON rp.id_games = g.id_games
        LEFT JOIN game_events ge ON ge.game_id = g.id_games
        WHERE rp.id_room_player IN (
          SELECT id_room_player 
          FROM detail_room_players_user 
          WHERE player1 = ? OR player2 = ?
        )";

      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ii", $userId, $userId);
      $stmt->execute();
      $result = $stmt->get_result();
      $datos = $result->fetch_assoc();

      $totalPartidas = $datos['total_partidas'] ?? 0;
      $partidasGanadas = $datos['partidas_ganadas'] ?? 0;
      ?>

      <div class="challenge-panel p-4 bg-dark bg-opacity-75 rounded shadow-lg">
        <h5 class="text-uppercase text-info mb-3">Historial Partidas</h5>
        <ul class="list-group list-group-flush mb-4">
          <li class="list-group-item bg-transparent text-light">
            Partidas jugadas: <strong><?= $totalPartidas ?></strong>
          </li>
          <li class="list-group-item bg-transparent text-light">
            Partidas ganadas: <strong><?= $partidasGanadas ?></strong>
          </li>
        </ul>

        
      </div>

    </div>
  </div>
</div>