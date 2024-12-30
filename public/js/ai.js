function evaluateBoard(board) {
    // 簡單的評估函數，計算每個方向的連續棋子
    const directions = [
        [0, 1],  // 水平
        [1, 0],  // 垂直
        [1, 1],  // 對角線
        [1, -1]  // 反對角線
    ];
    
    let score = 0;
    
    for (let i = 0; i < 15; i++) {
        for (let j = 0; j < 15; j++) {
            if (board[i][j] === 2) { // AI (白子)
                for (const [dx, dy] of directions) {
                    let count = 1;
                    let blocked = 0;
                    
                    // 向一個方向檢查
                    let x = i + dx;
                    let y = j + dy;
                    while (x >= 0 && x < 15 && y >= 0 && y < 15 && board[x][y] === 2) {
                        count++;
                        x += dx;
                        y += dy;
                    }
                    if (x < 0 || x >= 15 || y < 0 || y >= 15 || board[x][y] === 1) blocked++;
                    
                    // 向相反方向檢查
                    x = i - dx;
                    y = j - dy;
                    while (x >= 0 && x < 15 && y >= 0 && y < 15 && board[x][y] === 2) {
                        count++;
                        x -= dx;
                        y -= dy;
                    }
                    if (x < 0 || x >= 15 || y < 0 || y >= 15 || board[x][y] === 1) blocked++;
                    
                    // 根據連續棋子數和被封堵的端點數評分
                    if (count >= 5) score += 100000;
                    else if (count === 4 && blocked === 0) score += 10000;
                    else if (count === 4 && blocked === 1) score += 1000;
                    else if (count === 3 && blocked === 0) score += 1000;
                    else if (count === 3 && blocked === 1) score += 100;
                    else if (count === 2 && blocked === 0) score += 100;
                }
            }
            else if (board[i][j] === 1) { // 玩家（黑子）
                for (const [dx, dy] of directions) {
                    let count = 1;
                    let blocked = 0;
                    
                    // 向一個方向檢查
                    let x = i + dx;
                    let y = j + dy;
                    while (x >= 0 && x < 15 && y >= 0 && y < 15 && board[x][y] === 1) {
                        count++;
                        x += dx;
                        y += dy;
                    }
                    if (x < 0 || x >= 15 || y < 0 || y >= 15 || board[x][y] === 2) blocked++;
                    
                    // 向相反方向檢查
                    x = i - dx;
                    y = j - dy;
                    while (x >= 0 && x < 15 && y >= 0 && y < 15 && board[x][y] === 1) {
                        count++;
                        x -= dx;
                        y -= dy;
                    }
                    if (x < 0 || x >= 15 || y < 0 || y >= 15 || board[x][y] === 2) blocked++;
                    
                    // 根據連續棋子數和被封堵的端點數評分
                    if (count >= 5) score -= 100000;
                    else if (count === 4 && blocked === 0) score -= 10000;
                    else if (count === 4 && blocked === 1) score -= 1000;
                    else if (count === 3 && blocked === 0) score -= 1000;
                    else if (count === 3 && blocked === 1) score -= 100;
                    else if (count === 2 && blocked === 0) score -= 100;
                }
            }
        }
    }
    return score;
}

function findBestMove(board) {
    let bestScore = -Infinity;
    let bestMove = { row: -1, col: -1 };
    
    // 只考慮當前棋子周圍的空位
    const positions = [];
    for (let i = 0; i < 15; i++) {
        for (let j = 0; j < 15; j++) {
            if (board[i][j] !== 0) {
                // 檢查周圍8個方向的空位
                for (let di = -1; di <= 1; di++) {
                    for (let dj = -1; dj <= 1; dj++) {
                        if (di === 0 && dj === 0) continue;
                        
                        const newI = i + di;
                        const newJ = j + dj;
                        
                        if (newI >= 0 && newI < 15 && newJ >= 0 && newJ < 15 && 
                            board[newI][newJ] === 0 &&
                            !positions.some(p => p.row === newI && p.col === newJ)) {
                            positions.push({ row: newI, col: newJ });
                        }
                    }
                }
            }
        }
    }
    
    // 如果沒有找到任何位置（第一步），就選擇棋盤中心
    if (positions.length === 0) {
        return { row: 7, col: 7 };
    }
    
    // 評估每個可能的位置
    for (const pos of positions) {
        board[pos.row][pos.col] = 2; // AI移動
        const score = evaluateBoard(board);
        board[pos.row][pos.col] = 0; // 撤銷移動
        
        if (score > bestScore) {
            bestScore = score;
            bestMove = pos;
        }
    }
    
    return bestMove;
} 