<?php
 // Global database/session initialization.
 require_once "./config.php";

 function split_token(string $token): ?array
 {
    $parts = explode(':', $token);

    if ($parts && count($parts) == 2) {
        return [$parts[0], $parts[1]];
    }
    return null;
 }

 function find_token_by_selector(string $selector, $pdo)
 {
     $statement = $pdo->prepare('SELECT id, token_selector, token_validator, account_id, expiry
                FROM nm_logins
                WHERE token_selector = :selector AND expiry >= now()
                LIMIT 1');
     $statement->bindValue(':selector', $selector);
     $statement->execute();

     return $statement->fetch(PDO::FETCH_ASSOC);
 }

 function token_is_valid(string $token, $pdo): bool {
  [$selector_cookie, $validator_cookie] = split_token($token);
   $token_db = find_token_by_selector($selector_cookie, $pdo);
   if (!$token_db) {
    return false;
   }
  return password_verify($validator_cookie, $token_db['token_validator']);
 }

 function find_user_by_token(string $token, $pdo) {
    $tokens = split_token($token);

    if (!$tokens) {
        return null;
    }

    $statement = $pdo->prepare('SELECT accounts.id FROM accounts
            INNER JOIN nm_logins ON account_id = accounts.id
            WHERE token_selector = :selector AND expiry > now()
            LIMIT 1');
    $statement->bindValue(':selector', $tokens[0]);
    $statement->execute();

    return $statement->fetch(PDO::FETCH_ASSOC);
 }

 function delete_token($token, $pdo) {
  [$selector_cookie, $validator_cookie] = split_token($token);
  $statement = $pdo->prepare("DELETE FROM nm_logins WHERE token_selector = :selector");
  $statement->bindValue(':selector', $selector_cookie);
  $statement->execute();
 }
?>
