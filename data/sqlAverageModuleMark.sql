SELECT ROUND(AVG(moduleResult),0) as averageMark 
FROM ubihol01db.moduleResults
WHERE moduleCode = 'dt';